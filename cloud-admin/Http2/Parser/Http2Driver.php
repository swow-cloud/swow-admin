<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Http2\Parser;

use CloudAdmin\Http2\Exception\ClientException;
use CloudAdmin\Http2\Exception\Http2ConnectionException;
use CloudAdmin\Http2\Exception\Http2StreamException;
use CloudAdmin\Http2\HPack\HPack;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function array_intersect_key;
use function array_merge;
use function array_pop;
use function array_shift;
use function assert;
use function gmdate;
use function implode;
use function in_array;
use function is_callable;
use function is_int;
use function is_scalar;
use function max;
use function min;
use function pack;
use function preg_match;
use function sprintf;
use function str_split;
use function strlen;
use function strpos;
use function substr;
use function time;
use function var_dump;

final class Http2Driver
{
    public const DEFAULT_MAX_FRAME_SIZE = 1 << 14;

    public const DEFAULT_WINDOW_SIZE = (1 << 16) - 1;

    // 服务端本地窗口警戒值，到了这个值就应该通知客户端增加窗口
    private const MINIMUM_WINDOW = (1 << 15) - 1;

    private const MAX_INCREMENT = (1 << 16) - 1;

    private const PUSH_PROMISE_INTERSECT = [
        'accept' => true,
        'accept-charset' => true,
        'accept-encoding' => true,
        'accept-language' => true,
        'authorization' => true,
        'cache-control' => true,
        'cookie' => true,
        'date' => true,
        'host' => true,
        'user-agent' => true,
        'via' => true,
    ];

    /** @var string 64-bit for ping. */
    private string $counter = 'zxxzxxxx';

    /** @var int 整个链服务端接剩余窗口 */
    private int $serverWindow = self::DEFAULT_WINDOW_SIZE;

    /** @var int 整个链客户端端剩余窗口 */
    private int $clientWindow = self::DEFAULT_WINDOW_SIZE;

    /** @var int 客户端设置的初始窗口大小  流级别流控制的初始窗口大小 */
    private int $initialWindowSize = self::DEFAULT_WINDOW_SIZE;

    /** @var int 客户端允许的最大帧大小 */
    private int $maxFrameSize = self::DEFAULT_MAX_FRAME_SIZE;

    /** @var bool 客户端是否允许服务端推送 */
    private bool $allowsPush;

    /** @var int Last used local stream ID. */
    private int $localStreamId = 0;

    /** @var int Last used remote stream ID. */
    private int $remoteStreamId = 0;

    /** @var Http2Stream[] */
    private array $streams = [];

    /**
     * 本地支持的剩余流数量.
     * @var int number of streams that may be opened
     */
    private int $remainingStreams;

    /** @var int[] */
    private array $streamIdRequestMap = [];

    /** @var int[] */
    private array $streamIdResponseMap = [];

    /** @var int 客户端ping服务端的次数 */
    private int $pinged = 0;

    /** @var callable */
    private $onRequest;

    /** @var callable */
    private $onStreamData;

    /**
     * @var callable
     */
    private $onWriteBody;

    public function __construct(private readonly Http2Connection $http2Connection, ?callable $onStreamData, ?callable $onRequest, ?callable $onWriteBody, private readonly array $streamUrl, private readonly HPack $hPack)
    {
        $this->remainingStreams = Options::getConcurrentStreamLimit();
        $this->allowsPush = Options::isPushEnabled();
        $this->onRequest = $onRequest;
        $this->onStreamData = $onStreamData;
        $this->onWriteBody = $onWriteBody;
    }

    public function stop(): void
    {
        $this->shutdown();
    }

    public function send(int $id, Response $response, Request $request)
    {
        try {
            $this->sendHeader($id, $response, $request);
            $pushStream = [];
            if ($this->allowsPush) {
                foreach ($response->getPushes() as $push) {
                    // 推送帧构造
                    $pushStream[] = $this->sendPushPromise($request, $id, $push);
                }
            }
            if ($request->getMethod() === 'HEAD') {
                $this->streams[$id]->state |= Http2Stream::LOCAL_CLOSED;
                $this->writeData('', $id);
                return;
            }
            $this->sendBody($id, $response, $request);
            if (!empty($pushStream)) {
                foreach ($pushStream as $push) {// 发送推送帧
                    $this->send(...$push);
                }
            }
        } catch (ClientException $exception) {
            $error = $exception->getCode() ?? Http2Parser::CANCEL;
            $error ??= Http2Parser::INTERNAL_ERROR;
            $this->writeFrame(pack('N', $error), Http2Parser::RST_STREAM, Http2Parser::NO_FLAG, $id);
            $this->releaseStream($id, $exception ?? new ClientException('Stream error', $error));
        } finally {
            if ($this->streams[$id]->state & Http2Stream::REMOTE_CLOSED) {
                $this->releaseStream($id);
            }
        }
    }

    public function sendBody(int $id, Response $response, Request $request): void
    {
        if (!in_array($request->path(), $this->streamUrl)) {
            if (is_callable($this->onWriteBody)) {
                // 服务端流模式在此处不停的发送帧
                $response->http2Driver = $this;
                $response->streamId = $id;
                ($this->onWriteBody)($request, $response);
            }
        }
        $body = $response->getBody();
        if (strlen((string)$body)) {
            $this->writeData($body, $id);
        }
        if (!isset($this->streams[$id])) {
            return;
        }
        $trailers = $response->getTrailers();
        if ($trailers) {
            $this->streams[$id]->state |= Http2Stream::LOCAL_CLOSED;
            $headers = $this->encodeHeaders($trailers);
            if (strlen($headers) > $this->maxFrameSize) {
                $split = str_split($headers, $this->maxFrameSize);
                $headers = array_shift($split);
                $this->writeFrame($headers, Http2Parser::HEADERS, Http2Parser::NO_FLAG, $id);
                $headers = array_pop($split);
                foreach ($split as $msgPart) {
                    $this->writeFrame($msgPart, Http2Parser::CONTINUATION, Http2Parser::NO_FLAG, $id);
                }
                $this->writeFrame($headers, Http2Parser::CONTINUATION, Http2Parser::END_HEADERS | Http2Parser::END_STREAM, $id);
            } else {
                $this->writeFrame($headers, Http2Parser::HEADERS, Http2Parser::END_HEADERS | Http2Parser::END_STREAM, $id);
            }
        }
    }

    /**
     * @throws ClientException
     */
    public function sendHeader(int $id, Response $response, Request $request): void
    {
        $status = $response->getStatus();
        if ($status < 200) {
            $response->setStatus(505);
            throw new ClientException('1xx response codes are not supported in HTTP/2', Http2Parser::HTTP_1_1_REQUIRED);
        }
        $headers = array_merge([':status' => $status], $response->getHeaders());
        unset($headers['connection'], $headers['keep-alive'], $headers['transfer-encoding']);
        $headers['date'] = [self::formatDateHeader()];
        $trailers = $response->getTrailers();
        // if (empty($trailers)) {
        //    $headers["content-length"] = [strlen($response->getBody())];
        // } else {
        //    $headers["transfer-encoding"] = ["chunked"];  //HTTP2 是没有 chunked 的！
        // }
        // 对于 HTTP/1.1POST请求，客户端应该发送Content-Length标头，请参阅https://datatracker.ietf.org/doc/html/rfc7230#section-3.3.2。
        // 对于 HTTP/2，Content-Length标头不是强制性的（请参阅https://datatracker.ietf.org/doc/html/rfc7540#section-8.1.2.6），因为该协议具有endStream指示内容结束的标志。
        $headers['server'] = ['swow-http2'];
        $headers = $this->encodeHeaders($headers);
        if (strlen($headers) > $this->maxFrameSize) {
            $split = str_split($headers, $this->maxFrameSize);
            $headers = array_shift($split);
            $this->writeFrame($headers, Http2Parser::HEADERS, Http2Parser::NO_FLAG, $id);
            $headers = array_pop($split);
            foreach ($split as $msgPart) {
                $this->writeFrame($msgPart, Http2Parser::CONTINUATION, Http2Parser::NO_FLAG, $id);
            }
            $this->writeFrame($headers, Http2Parser::CONTINUATION, Http2Parser::END_HEADERS, $id);
        } else {
            $this->writeFrame($headers, Http2Parser::HEADERS, Http2Parser::END_HEADERS, $id);
        }
        if (empty($trailers)) { // 设置本地关闭状态，在data流里面就发送END_STREAM否则就在发送$trailers时候发送END_STREAM
            if (isset($this->streams[$id])) {
                $this->streams[$id]->state |= Http2Stream::LOCAL_CLOSED;
            }
        }
    }

    /**
     * http 响应体也就是data帧这里是缓冲区，其他的帧不存在缓冲区 写一次缓冲区触发一次写帧操作.
     */
    public function writeData(string $data, int $id): void
    {
        if (!isset($this->streams[$id])) {
            var_dump('The stream was closed');
            return;
        }
        $this->streams[$id]->buffer .= $data;
        $this->writeBufferedData($id);
    }

    public function writeFrame(string $data, int $type, int $flags, int $stream = 0): void
    {
        $this->http2Connection->connection->send(substr(pack('NccN', strlen($data), $type, $flags, $stream), 1) . $data);
    }

    public function handlePing(string $data): void
    {
        if (!$this->pinged) { // 没有ping过就更新
            $this->http2Connection->updateExpirationTime(
                max($this->http2Connection->getExpirationTime(), time() + 5)
            );
        }
        ++$this->pinged;
        $this->writeFrame($data, Http2Parser::PING, Http2Parser::ACK);
    }

    /**
     * @throws Http2ConnectionException
     * @throws Http2StreamException
     */
    public function handleStreamWindowIncrement(int $streamId, int $windowSize): void
    {
        if ($streamId > $this->remoteStreamId) {
            throw new Http2ConnectionException('Stream ID does not exist', Http2Parser::PROTOCOL_ERROR);
        }
        if (!isset($this->streams[$streamId])) {
            return;
        }
        $stream = $this->streams[$streamId];
        if ($stream->clientWindow + $windowSize > 2_147_483_647) {
            throw new Http2StreamException('Current window size plus new window exceeds maximum size', $streamId, Http2Parser::FLOW_CONTROL_ERROR);
        }
        $stream->clientWindow += $windowSize;
        $this->sendBufferedData();
    }

    public function handlePong(string $data): void
    {
        // 无事可做
    }

    public static function formatDateHeader(?int $timestamp = null): string
    {
        static $cachedTimestamp, $cachedFormattedDate;
        $timestamp ??= time();
        if ($cachedTimestamp === $timestamp) {
            return $cachedFormattedDate;
        }
        return $cachedFormattedDate = gmdate('D, d M Y H:i:s', $cachedTimestamp = $timestamp) . ' GMT';
    }

    /**
     * 链接窗口 增加某个值
     * @throws Http2ConnectionException
     */
    public function handleConnectionWindowIncrement(int $windowSize): void
    {
        if ($this->clientWindow + $windowSize > 2_147_483_647) {
            throw new Http2ConnectionException('Current window size plus new window exceeds maximum size', Http2Parser::FLOW_CONTROL_ERROR);
        }
        $this->clientWindow += $windowSize;
        $this->sendBufferedData();
    }

    /**
     * @throws Http2StreamException
     * @throws ClientException
     * @throws Http2ConnectionException
     */
    public function handleHeaders(int $streamId, array $pseudo, array $headers, bool $ended): void
    {
        foreach ($pseudo as $name => $value) {
            if (!isset(Http2Parser::KNOWN_REQUEST_PSEUDO_HEADERS[$name])) {
                throw new Http2StreamException('Invalid pseudo header', $streamId, Http2Parser::PROTOCOL_ERROR);
            }
        }
        if (isset($this->streams[$streamId])) {
            $stream = $this->streams[$streamId];
            if ($stream->state & Http2Stream::REMOTE_CLOSED) {
                throw new Http2StreamException('Stream remote closed', $streamId, Http2Parser::STREAM_CLOSED);
            }
        } else {
            if (!($streamId & 1) || $this->remainingStreams-- <= 0 || $streamId <= $this->remoteStreamId) {
                throw new Http2ConnectionException("Invalid stream ID {$streamId}", Http2Parser::PROTOCOL_ERROR);
            }
            $stream = $this->streams[$streamId] = new Http2Stream(Options::getBodySizeLimit(), $this->initialWindowSize);
        }
        $this->remoteStreamId = max($streamId, $this->remoteStreamId);
        $this->http2Connection->updateExpirationTime(time() + Options::getHttp2Timeout());
        if ($stream->state & Http2Stream::RESERVED) {
            throw new Http2StreamException('Stream already reserved', $streamId, Http2Parser::PROTOCOL_ERROR);
        }
        $stream->state |= Http2Stream::RESERVED;
        if (!isset($pseudo[':method'], $pseudo[':path'], $pseudo[':scheme'], $pseudo[':authority'])
            || isset($headers['connection'])
            || $pseudo[':path'] === ''
            || (isset($headers['te']) && implode('', $headers['te']) !== 'trailers')
        ) {
            throw new Http2StreamException('Invalid header values', $streamId, Http2Parser::PROTOCOL_ERROR);
        }
        $method = $pseudo[':method'];
        $target = $pseudo[':path'];
        $scheme = $pseudo[':scheme'];
        $host = $pseudo[':authority'];
        $query = null;
        if (!preg_match('#^([A-Z\\d\\.\\-]+|\\[[\\d:]+\\])(?::([1-9]\\d*))?$#i', (string)$host, $matches)) {
            throw new Http2StreamException('Invalid authority (host) name', $streamId, Http2Parser::PROTOCOL_ERROR);
        }
        $host = $matches[1];
        $port = isset($matches[2]) ? (int)$matches[2] : $this->http2Connection->getPort();
        if ($position = strpos((string)$target, '#')) {
            $target = substr((string)$target, 0, $position);
        }
        if ($position = strpos((string)$target, '?')) {
            $query = substr((string)$target, $position + 1);
            $target = substr((string)$target, 0, $position);
        }
        $headers = array_merge($headers, ['scheme' => $scheme, 'host' => $host, 'port' => $port, 'path' => $target, 'query' => $query, 'method' => $method]);
        $this->pinged = 0;
        if ($ended) {  // 如果是结束流表示没有请求体
            $request = new Request($streamId, $this->http2Connection, $headers);
            $this->streamIdRequestMap[$streamId] = $request;
            return;
        }
        $maxBodySize = Options::getBodySizeLimit();
        if ($this->serverWindow <= $maxBodySize >> 1) {
            $increment = $maxBodySize - $this->serverWindow;
            $this->serverWindow = $maxBodySize;
            $this->writeFrame(pack('N', $increment), Http2Parser::WINDOW_UPDATE, Http2Parser::NO_FLAG);
        }
        if (isset($headers['content-length'])) {
            if (isset($headers['content-length'][1])) {
                throw new Http2StreamException('Received multiple content-length headers', $streamId, Http2Parser::PROTOCOL_ERROR);
            }
            $contentLength = $headers['content-length'][0];
            if (!preg_match('/^0|[1-9][0-9]*$/', (string)$contentLength)) {
                throw new Http2StreamException('Invalid content-length header value', $streamId, Http2Parser::PROTOCOL_ERROR);
            }
            $stream->expectedLength = (int)$contentLength;
        }
        $request = new Request($streamId, $this->http2Connection, $headers);
        $this->streamIdRequestMap[$streamId] = $request;
        // 如果是客户端流模式，这里应该给前端返回头响应
        if (in_array($request->path(), $this->streamUrl)) {
            if (is_callable($this->onRequest)) {
                $response = ($this->onRequest)($request,$this->http2Connection);
            } else {
                $response = new Response(200, ['content-type' => 'text/html'], '');
            }
            $response->http2Driver = $this;
            $response->streamId = $streamId;
            $this->streamIdResponseMap[$streamId] = $response;
            $this->sendHeader($streamId, $response, $request);
        }
    }

    /**
     * 所有handle开头的方法都是前端过来的帧处理逻辑.
     * @throws Http2StreamException
     */
    public function handleData(int $streamId, string $data): void
    {
        $length = strlen($data);
        $this->http2Connection->updateExpirationTime(time() + Options::getHttp2Timeout());
        $stream = $this->streams[$streamId];
        if ($stream->state & Http2Stream::REMOTE_CLOSED) {
            throw new Http2StreamException('Stream remote closed', $streamId, Http2Parser::PROTOCOL_ERROR);
        }
        if (!$length) {
            return;
        }
        $this->serverWindow -= $length;
        $stream->serverWindow -= $length;
        $stream->received += $length;
        if ($stream->received > $stream->maxBodySize) {
            throw new Http2StreamException('Max body size exceeded', $streamId, Http2Parser::CANCEL);
        }
        if ($this->serverWindow <= self::MINIMUM_WINDOW) {
            $this->serverWindow += self::MAX_INCREMENT;
            $this->writeFrame(pack('N', self::MAX_INCREMENT), Http2Parser::WINDOW_UPDATE, Http2Parser::NO_FLAG);
        }
        if (is_int($stream->expectedLength)) {
            $stream->expectedLength -= $length;
        }
        /** @var Request $request */
        $request = $this->streamIdRequestMap[$streamId];
        if (in_array($request->path(), $this->streamUrl)) {
            // 客户端流模式在此处不停的发送帧  需要先把头信息发送回去再发送data帧
            // $this->streams[$streamId]
            if (is_callable($this->onStreamData)) {
                if (($this->onStreamData)($request, $this->streamIdResponseMap[$streamId], $data) === false) {
                    // if (($this->onStreamData)($request, $this->streams[$streamId], $data) === false) {
                    // 如果服务端模式。服务端准备关闭流，就假设这是最后一个流
                    $this->handleStreamEnd($streamId);
                }
            }
        } else {
            $request->appendData($data);
        }
        if ($stream->serverWindow <= self::MINIMUM_WINDOW) {
            if (!isset($this->streams[$streamId])) {
                return;
            }
            $stream = $this->streams[$streamId];
            if ($stream->state & Http2Stream::REMOTE_CLOSED || $stream->serverWindow > self::MINIMUM_WINDOW) {
                return;
            }
            $increment = min($stream->maxBodySize + 1 - $stream->received - $stream->serverWindow, self::MAX_INCREMENT);
            if ($increment <= 0) {
                return;
            }
            $stream->serverWindow += $increment;
            $this->writeFrame(pack('N', $increment), Http2Parser::WINDOW_UPDATE, Http2Parser::NO_FLAG, $streamId);
        }
    }

    /**
     * @throws Http2StreamException
     */
    public function handleStreamEnd(int $streamId): void
    {
        if (!isset($this->streams[$streamId])) {
            return;
        }
        $stream = $this->streams[$streamId];
        $stream->state |= Http2Stream::REMOTE_CLOSED;
        if ($stream->expectedLength) {    // 预期还需要收到的数据长度
            throw new Http2StreamException('Body length does not match content-length header', $streamId, Http2Parser::PROTOCOL_ERROR);
        }
        /** @var Request $request */
        $request = $this->streamIdRequestMap[$streamId];
        if (!in_array($request->getMethod(), Options::getAllowedMethods())) {
            $response = new Response(405); // METHOD_NOT_ALLOWED
        } else {
            $response = $this->streamIdResponseMap[$streamId] ?? null;
            if (!$response instanceof Response) {
                if (is_callable($this->onRequest)) {
                    $response = ($this->onRequest)($request,$this->http2Connection);
                }
            }
            if (!$response instanceof Response) {
                $response = new Response(404, ['content-type' => 'text/html'], '');
            }
        }
        if (in_array($request->path(), $this->streamUrl)) {
            // 客户端是流传输data过来,因为已经把header传输回去了，当前只需要传递body回去
            $this->sendBody($streamId, $response, $request);
        } else {
            $this->send($streamId, $response, $request);
        }
        // if (!isset($this->streams[$streamId])) {
        //    return;
        // }
        if ($stream->state & Http2Stream::LOCAL_CLOSED && $stream->buffer === '') {
            $this->releaseStream($streamId);
        }
    }

    /**
     * @throws Http2ConnectionException
     */
    public function handlePushPromise(int $streamId, int $pushId, array $pseudo, array $headers): never
    {
        throw new Http2ConnectionException('Client should not send push promise frames', Http2Parser::PROTOCOL_ERROR);
    }

    /**
     * 流的优先级 也可以为将来的流设置优先级.
     * @throws Http2ConnectionException
     */
    public function handlePriority(int $streamId, int $parentId, int $weight): void
    {
        if (!isset($this->streams[$streamId])) {// 为将来的流设置优先级
            // if ($streamId === 0 || !($streamId & 1) || $this->remainingStreams-- <= 0) {
            if ($streamId === 0 || $this->remainingStreams-- <= 0) {  // chrome居然发送了偶数帧过来
                throw new Http2ConnectionException("Invalid stream ID {$streamId}", Http2Parser::PROTOCOL_ERROR);
            }
            if ($streamId <= $this->remoteStreamId) {// 此帧可能在处理完成或帧发送完成后到达，这会导致它对已识别的流没有任何影响
                return;
            }
            $this->streams[$streamId] = new Http2Stream(Options::getBodySizeLimit(), $this->initialWindowSize);
        }
        $stream = $this->streams[$streamId];
        $stream->dependency = $parentId;
        $stream->weight = $weight;
    }

    public function handleStreamReset(int $streamId, int $errorCode): void
    {
        if ($streamId > $this->remoteStreamId) {
            // throw new Http2ConnectionException("Invalid stream ID $streamId", Http2Parser::PROTOCOL_ERROR);
        }
        if (isset($this->streams[$streamId])) {
            $exception = new Http2StreamException('Stream reset', $streamId, $errorCode);
            $this->releaseStream($streamId, new ClientException('Client closed stream', $errorCode, $exception));
        }
    }

    // 接收设置帧

    /**
     * @throws Http2ConnectionException
     */
    public function handleSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            switch ($key) {
                case Http2Parser::INITIAL_WINDOW_SIZE:
                    if ($value > 2_147_483_647) {
                        throw new Http2ConnectionException('Invalid window size', Http2Parser::FLOW_CONTROL_ERROR);
                    }
                    $priorWindowSize = $this->initialWindowSize;  // 上次设置的的的大小
                    $this->initialWindowSize = $value;
                    $difference = $this->initialWindowSize - $priorWindowSize;
                    foreach ($this->streams as $stream) {
                        $stream->clientWindow += $difference;  // 更新剩余大小
                    }
                    $this->sendBufferedData();
                    break;
                case Http2Parser::ENABLE_PUSH:
                    if ($value & ~1) {
                        throw new Http2ConnectionException('Invalid push promise toggle value', Http2Parser::PROTOCOL_ERROR);
                    }
                    $this->allowsPush = $value && Options::isPushEnabled();
                    break;
                case Http2Parser::MAX_FRAME_SIZE:
                    if ($value < 1 << 14 || $value >= 1 << 24) {
                        throw new Http2ConnectionException('Invalid max frame size', Http2Parser::PROTOCOL_ERROR);
                    }
                    $this->maxFrameSize = $value;
                    break;
                case Http2Parser::MAX_CONCURRENT_STREAMS:
                case Http2Parser::MAX_HEADER_LIST_SIZE:
                case Http2Parser::HEADER_TABLE_SIZE:
                default:
                    break;
            }
        }
        $this->writeFrame('', Http2Parser::SETTINGS, Http2Parser::ACK);
    }

    /**
     * 流异常.
     */
    public function handleStreamException(Http2StreamException $exception): void
    {
        $streamId = $exception->getStreamId();
        $errorCode = $exception->getCode();
        $this->writeFrame(pack('N', $errorCode), Http2Parser::RST_STREAM, Http2Parser::NO_FLAG, $streamId);
        if (isset($this->streams[$streamId])) {
            $this->releaseStream($streamId, new ClientException('HTTP/2 stream error', 0, $exception));
        }
    }

    /**
     * 链接异常.
     */
    public function handleConnectionException(Http2ConnectionException $exception): void
    {
        $message = sprintf(
            "HTTP/2 connection error for client %s: %s\r\n",
            $this->http2Connection->getRemoteAddress(),
            $exception->getMessage()
        );
        $this->shutdown(null, new ClientException('HTTP/2 connection error', $exception->getCode(), $exception));
    }

    // http1.1升级h2c

    /**
     * @throws Http2StreamException
     */
    public function upgrade(Http2Connection $connection, ServerRequestInterface $request): void
    {
        $header = $request->getHeaders();
        $header['method'] = $request->getMethod();
        $header['uri'] = $request->getUri();
        $header['queryString'] = $request->getQueryParams();
        $header['path'] = $request->getUri()->getPath();
        $this->streams[1] = new Http2Stream(
            0,
            $this->initialWindowSize,
            Http2Stream::RESERVED | Http2Stream::REMOTE_CLOSED
        );
        $this->streamIdRequestMap[1] = new Request(1, $connection, $header, $request->getBody()->getContents());
        --$this->remainingStreams;
        $this->handleStreamEnd(1);
    }

    private function sendPushPromise(Request $request, int $streamId, array $push): array
    {
        $mergedHeaders = array_merge(array_intersect_key($request->header(), self::PUSH_PROMISE_INTERSECT), $push['header']);
        $id = $this->localStreamId += 2;
        // $this->remoteStreamId = \max($id, $this->remoteStreamId);
        $headers = array_merge(['scheme' => 'https', 'host' => '', 'port' => 443, 'path' => '/', 'query' => '', 'method' => 'GET'], $push['uri'], $mergedHeaders);
        $request = new Request($streamId, $this->http2Connection, $headers);
        $this->streams[$id] = new Http2Stream(0, $this->initialWindowSize, Http2Stream::RESERVED | Http2Stream::REMOTE_CLOSED);
        $this->streamIdRequestMap[$id] = $request;
        foreach ($mergedHeaders as $k => $v) {
            if (is_scalar($v)) {
                $mergedHeaders[$k] = [$v];
            }
        }
        $headers = array_merge([
            ':authority' => [$headers['host']],
            ':scheme' => [$headers['scheme']],
            ':path' => [$headers['path']],
            ':method' => [$headers['method']],
        ], $mergedHeaders);
        $headers = pack('N', $id) . $this->encodeHeaders($headers);
        if (strlen($headers) >= $this->maxFrameSize) {
            $split = str_split($headers, $this->maxFrameSize);
            $headers = array_shift($split);
            $this->writeFrame($headers, Http2Parser::PUSH_PROMISE, Http2Parser::NO_FLAG, $streamId);
            $headers = array_pop($split);
            foreach ($split as $msgPart) {
                $this->writeFrame($msgPart, Http2Parser::CONTINUATION, Http2Parser::NO_FLAG, $id);
            }
            $this->writeFrame($headers, Http2Parser::CONTINUATION, Http2Parser::END_HEADERS, $id);
        } else {
            $this->writeFrame($headers, Http2Parser::PUSH_PROMISE, Http2Parser::END_HEADERS, $streamId);
        }
        $response = null;
        if (is_callable($this->onRequest)) {
            $response = ($this->onRequest)($request,$this->http2Connection);
        }
        if (!$response instanceof Response) {
            $response = new Response(404, ['content-type' => 'text/html'], '');
        }
        $this->streams[$id]->state |= Http2Stream::LOCAL_CLOSED;
        return [$id, $response, $request];
    }

    private function ping(): void
    {
        $this->writeFrame($this->counter++, Http2Parser::PING, Http2Parser::NO_FLAG);
    }

    /**
     * 窗口大小发生变化的时候检测一次是否需要发送body帧.
     */
    private function sendBufferedData(): void
    {
        foreach ($this->streams as $id => $stream) {
            if ($this->clientWindow <= 0) {
                return;
            }
            if (!strlen($stream->buffer) || $stream->clientWindow <= 0) {
                continue;
            }
            $this->writeBufferedData($id);
        }
    }

    private function writeBufferedData(int $id): void
    {
        assert(isset($this->streams[$id]), 'The stream was closed');
        $stream = $this->streams[$id];
        $delta = min($this->clientWindow, $stream->clientWindow);
        $length = strlen($stream->buffer);
        $this->http2Connection->updateExpirationTime(time() + Options::getHttp2Timeout());
        if ($delta >= $length) {
            $this->clientWindow -= $length;
            if ($length > $this->maxFrameSize) {
                $split = str_split($stream->buffer, $this->maxFrameSize);
                $stream->buffer = array_pop($split);
                foreach ($split as $part) {
                    $this->writeFrame($part, Http2Parser::DATA, Http2Parser::NO_FLAG, $id);
                }
            }
            if ($stream->state & Http2Stream::LOCAL_CLOSED) {
                $this->writeFrame($stream->buffer, Http2Parser::DATA, Http2Parser::END_STREAM, $id);
            } else {
                $this->writeFrame($stream->buffer, Http2Parser::DATA, Http2Parser::NO_FLAG, $id);
            }
            $stream->clientWindow -= $length;
            $stream->buffer = '';
            return;
        }
        if ($delta > 0) {
            $data = $stream->buffer;
            $end = $delta - $this->maxFrameSize;
            $stream->clientWindow -= $delta;
            $this->clientWindow -= $delta;
            for ($off = 0; $off < $end; $off += $this->maxFrameSize) {
                $this->writeFrame(substr($data, $off, $this->maxFrameSize), Http2Parser::DATA, Http2Parser::NO_FLAG, $id);
            }
            $this->writeFrame(substr($data, $off, $delta - $off), Http2Parser::DATA, Http2Parser::NO_FLAG, $id);
            $stream->buffer = substr($data, $delta);
        }
    }

    private function encodeHeaders(array $headers): string
    {
        $input = [];
        foreach ($headers as $field => $values) {
            $values = (array)$values;
            foreach ($values as $value) {
                $input[] = [(string)$field, (string)$value];
            }
        }
        return $this->hPack->encode($input);
    }

    private function shutdown(?int $lastId = null, ?Throwable $reason = null): void
    {
        $code = $reason ? $reason->getCode() : Http2Parser::GRACEFUL_SHUTDOWN;
        $lastId ??= $id ?? 0;
        $this->writeFrame(pack('NN', $lastId, $code), Http2Parser::GOAWAY, Http2Parser::NO_FLAG);
        if (!empty($this->streams)) {
            if (empty($reason)) {
                $exception = new ClientException('', 0, $reason);
            } else {
                $exception = new ClientException($reason->getMessage(), $reason->getCode(), $reason);
            }
            foreach ($this->streams as $id => $stream) {
                $this->releaseStream($id, $exception);
            }
        }
        $this->http2Connection->close();
    }

    /**
     * 释放流
     */
    private function releaseStream(int $id, ClientException $exception = null): void
    {
        if (!isset($this->streams[$id])) {
            return;
        }
        unset($this->streams[$id]);
        if ($id % 2) {
            ++$this->remainingStreams;
        }
    }

    public function handleShutdown(int $lastId, int $error): void
    {
        $message = \sprintf(
            "Received GOAWAY frame from %s with error code %d\r\n",
            $this->http2Connection->getRemoteAddress(),
            $error
        );
        if ($error !== Http2Parser::GRACEFUL_SHUTDOWN) {
            Http2Parser::Log($message);
        }
        $this->shutdown($lastId, new Http2ConnectionException($message, $error));
    }
}
