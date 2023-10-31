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

final class Options
{
    public static function isInDebugMode(): bool
    {
        return true;
    }

    public static function getHttp2Timeout(): int
    {
        return 60;
    }

    public static function getBodySizeLimit(): int
    {
        return 13107200;
    }

    public static function getHeaderSizeLimit(): int
    {
        return 32768;
    }

    public static function getConcurrentStreamLimit(): int
    {
        return 256;
    }

    public static function getAllowedMethods(): array
    {
        return ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'OPTIONS', 'DELETE'];
    }

    public static function isPushEnabled(): bool
    {
        return true;
    }

    public static function logFile(): string
    {
        return './log/http2.log';
    }
}
