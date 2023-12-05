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

use function array_merge;
use function explode;
use function file;
use function file_get_contents;
use function filemtime;
use function filesize;
use function gmdate;
use function is_array;
use function is_file;
use function parse_url;
use function pathinfo;
use function preg_match;
use function strtolower;
use function substr;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;

class Response
{
    public Http2Driver $http2Driver;

    public int $streamId;

    public array $push = [];

    /**
     * @var null
     */
    protected static mixed $mimeTypeMap;

    /**
     * Header data.
     */
    protected array $header;

    protected array $trailers = [];

    /**
     * Response constructor.
     */
    public function __construct(/**
     * Http status.
     */
    protected int $status = 200, array $headers = [], /**
     * Http body.
     */
    protected string $body = '')
    {
        foreach ($headers as $k => $v) {
            $this->header[strtolower($k)] = [$v];
        }
    }

    /**
     * Set header.
     * @return $this
     */
    public function header(string $name, string $value): static
    {
        $this->header[strtolower($name)] = [$value];
        return $this;
    }

    /**
     * Get headers.
     */
    public function getHeaders(mixed $name = null)
    {
        if ($name == null) {
            return $this->header ?? [];
        }
        return $this->header[$name] ?? '';
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody($body = '')
    {
        return $this->body = $body;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * 推送 预加载.
     */
    public function getPushes(): array
    {
        return $this->push;
    }

    public function push(string $url, array $header = [])
    {
        $urlInfo = parse_url($url);
        if (is_array($urlInfo)) {
            $this->push[] = [
                'uri' => $urlInfo,
                'header' => $header,
            ];
        }
    }

    public function getTrailers(): array
    {
        return $this->trailers;
        // return [
        //    "test" => "value",
        //    "test1" => "value",
        //    "test2" => "value"
        // ];
    }

    public function setTrailers(array $trailers): void
    {
        $this->trailers = array_merge($this->trailers, $trailers);
    }

    public function withFile($file, int $offset = 0, int $length = 0): void
    {
        if (! is_file($file)) {
            $this->status = 404;
            $this->header('content-type', 'text/html');
            $this->body = '<h3>404 Not Found</h3>';
            return;
        }
        $file_info = pathinfo((string) $file);
        $extension = $file_info['extension'] ?? '';
        $base_name = $file_info['basename'] ?? 'unknown';
        if (! isset($headers['content-type'])) {
            if (isset(self::$mimeTypeMap[$extension])) {
                $this->header('content-type', self::$mimeTypeMap[$extension]);
            } else {
                $this->header('content-type', 'application/octet-stream');
            }
        }
        if (! isset($headers['content-disposition']) && ! isset(self::$mimeTypeMap[$extension])) {
            $this->header('content-disposition', 'attachment; filename="' . $base_name . '"');
        }
        if (! isset($headers['last-modified'])) {
            if ($mtime = filemtime($file)) {
                $this->header('last-modified', gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
            }
        }
        $file_size = (int) filesize($file);
        $body_len = $length > 0 ? $length : $file_size - $offset;
        $this->header('accept-ranges', 'bytes');
        if ($offset || $length) {
            $offset_end = $offset + $body_len - 1;
            $this->header('content-range', "bytes {$offset}-{$offset_end}/{$file_size}");
        }
        if ($body_len < 2 * 1024 * 1024) {
            $this->body = file_get_contents($file, false, null, $offset, $body_len);
            return;
        }
        //        $this->body = function (){
        //            $a = $bodyLen / 1048576;
        //            $a = ceil($a);
        //            var_dump($a);
        //            for ($i = 0; $i < $a; $i++) {
        //                $f = $offset + 1048576 * $i;
        //                $len = min(1048576, $bodyLen - 1048576 * $i);
        //                if ($len < 1048576) {
        //                    $this->body = file_get_contents($file, false, null, $f, $bodyLen);
        //                } else {
        //                    $this->tuckData(file_get_contents($file, false, null, $f, min(1048576, $len)));
        //                }
        //                echo $f, "---", $bodyLen . "\r\n";
        //            }
        //        };
    }

    /**
     * 在响应前写入数据，只是在客户端非流式传输的时候有效.
     */
    public function tuckData(string $data): void
    {
        $this->http2Driver->writeData($data, $this->streamId);
    }

    public static function init(): void
    {
        $mime_file = __DIR__ . '/mime.types';
        $items = file($mime_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($items as $content) {
            if (preg_match('/\\s*(\\S+)\\s+(\\S.+)/', $content, $match)) {
                $mime_type = $match[1];
                $extension_var = $match[2];
                $extension_array = explode(' ', substr($extension_var, 0, -1));
                foreach ($extension_array as $file_extension) {
                    static::$mimeTypeMap[$file_extension] = $mime_type;
                }
            }
        }
    }
}

Response::init();
