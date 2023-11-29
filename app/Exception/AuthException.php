<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Exception;

use Hyperf\HttpMessage\Exception\HttpException;
use Swow\Http\Status;
use Throwable;

final class AuthException extends HttpException
{
    public function __construct(int $code = Status::UNAUTHORIZED, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct(statusCode: $code, message: $message);
    }
}
