<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Exception;

use App\Constants\ErrorCodeInterface;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class BusinessException extends ServerException
{
    public function __construct(ErrorCodeInterface $code, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = $code->getMessage();
        }

        parent::__construct($message, $code->value, $previous);
    }
}
