<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Http2\Exception;

use Exception;
use Throwable;

final class Http2StreamException extends Exception
{
    public function __construct(string $message, private readonly int $streamId, int $code, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }
}
