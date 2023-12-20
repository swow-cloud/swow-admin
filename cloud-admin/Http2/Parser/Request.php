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

use function explode;
use function file_put_contents;
use function is_array;
use function json_decode;
use function parse_str;
use function preg_match;
use function str_ends_with;
use function strlen;
use function strtolower;
use function substr;
use function tempnam;
use function trim;

/**
 * 不准备支持cookie  session.
 */
//todo 适配psr规范  PSR-7 PSR-15
class Request
{
    /**
     * Request $header.
     */
    protected array $header;

    /**
     * 里面是body解析出来的数据.
     */
    protected mixed $data;

    /**
     * Request constructor.
     */
    public function __construct(protected int $streamId, protected Http2Connection $client, mixed $header, protected mixed $rawBody = '')
    {
        foreach ($header as $key => $value) {
            if (is_array($value)) {
                $header[$key] = $value[0] ?? '';
            }
        }
        $this->header = $header;
    }

    public function appendData($_body): void
    {
        $this->rawBody .= $_body;
    }

    /**
     * @param null $name
     * @param null $default
     */
    public function get($name = null, $default = null): array
    {
        parse_str($this->header['query'] ?? '', $get);
        if ($name === null) {
            return $get;
        }
        return $get[$name] ?? $default;
    }

    /**
     * @param null $name
     * @param null $default
     * @return null|array|mixed|string
     */
    public function post($name = null, $default = null): mixed
    {
        if (!isset($this->data['post'])) {
            try {
                $this->parsePost();
            } catch (\JsonException $e) {
            }
        }
        if ($name === null) {
            return $this->data['post'] ?? [];
        }
        return $this->data['post'][$name] ?? $default;
    }

    /**
     * @param null $name
     * @return null|array|mixed
     */
    public function file($name = null): mixed
    {
        if (!isset($this->data['files'])) {
            $this->parsePost();
        }
        if ($name === null) {
            return $this->data['files'] ?? [];
        }
        return $this->data['files'][$name] ?? null;
    }

    public function ip(): string
    {
        return $this->client->getLocalAddress();
    }

    public function host(): string
    {
        return $this->header['host'];
    }

    /**
     * Get path.
     */
    public function path(): string
    {
        return $this->header['path'] ?? '/';
    }

    /**
     * Get query string.
     * @return mixed|string
     */
    public function queryString(): mixed
    {
        return $this->header['query'] ?? '';
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function getMethod(): string
    {
        return $this->header['method'];
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }

    public function header(string $name = null, mixed $default = null): null|array|string
    {
        if ($name === null) {
            return $this->header;
        }
        $name = strtolower($name);
        return $this->header[$name] ?? $default;
    }

    protected function parsePost(): void
    {
        if (!$this->rawBody) {
            return;
        }
        $this->data['post'] = $this->data['files'] = [];
        $content_type = $this->header('content-type');
        if ($content_type and preg_match('/boundary="?(\S+)"?/', $content_type, $match)) {
            $http_post_boundary = '--' . $match[1];
            $this->parseUploadFiles($http_post_boundary);
            return;
        }
        if (preg_match('/\bjson\b/i', $content_type)) {
            $this->data['post'] = (array)json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
        } else {
            parse_str($this->rawBody, $this->data['post']);
        }
    }

    /**
     * Parse upload files.
     */
    protected function parseUploadFiles(string $http_post_boundary): void
    {
        $http_post_boundary = trim($http_post_boundary, '"');
        $http_body = $this->rawBody();
        $http_body = substr($http_body, 0, strlen($http_body) - (strlen($http_post_boundary) + 4));
        $boundary_data_array = explode($http_post_boundary . "\r\n", $http_body);
        if ($boundary_data_array[0] === '' || $boundary_data_array[0] === "\r\n") {
            unset($boundary_data_array[0]);
        }
        $key = -1;
        $files = [];
        foreach ($boundary_data_array as $boundary_data_buffer) {
            [$boundary_header_buffer, $boundary_value] = explode("\r\n\r\n", $boundary_data_buffer, 2);
            // Remove \r\n from the end of buffer.
            $boundary_value = substr($boundary_value, 0, -2);
            ++$key;
            foreach (explode("\r\n", $boundary_header_buffer) as $item) {
                [$header_key, $header_value] = explode(': ', $item);
                $header_key = strtolower($header_key);
                switch ($header_key) {
                    case 'content-disposition':
                        // Is file data.
                        if (preg_match('/name="(.*?)"; filename="(.*?)"/i', $header_value, $match)) {
                            $error = 0;
                            $tmp_file = '';
                            $size = strlen($boundary_value);
                            $tmp_upload_dir = './temp/';
                            if (!$tmp_upload_dir) {
                                $error = UPLOAD_ERR_NO_TMP_DIR;
                            } else {
                                $tmp_file = tempnam($tmp_upload_dir, 'workerman.upload.');
                                if ($tmp_file === false || !file_put_contents($tmp_file, $boundary_value)) {
                                    $error = UPLOAD_ERR_CANT_WRITE;
                                }
                            }
                            if (!isset($files[$key])) {
                                $files[$key] = [];
                            }
                            // Parse upload files.
                            $files[$key] += [
                                'key' => $match[1],
                                'name' => $match[2],
                                'tmp_name' => $tmp_file,
                                'size' => $size,
                                'error' => $error,
                            ];
                            break;
                        } // Is post field.

                        // Parse $_POST.
                        if (preg_match('/name="(.*?)"$/', $header_value, $match)) {
                            $this->data['post'][$match[1]] = $boundary_value;
                        }

                        break;
                    case 'content-type':
                        // add file_type
                        if (!isset($files[$key])) {
                            $files[$key] = [];
                        }
                        $files[$key]['type'] = trim($header_value);
                        break;
                }
            }
        }
        foreach ($files as $file) {
            $key = $file['key'];
            unset($file['key']);
            // Multi files
            if (strlen($key) > 2 && str_ends_with($key, '[]')) {
                $key = substr($key, 0, -2);
                $this->data['files'][$key][] = $file;
            } else {
                $this->data['files'][$key] = $file;
            }
        }
    }
}
