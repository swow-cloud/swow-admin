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

use App\Constants\ErrorCodeInterface;
use Hyperf\Server\Exception\ServerException;
use Throwable;

use function is_null;

final class BusinessException extends ServerException
{
    public bool $errMsgIsFromErrorCode = false;

    public function __construct(protected ErrorCodeInterface $errorCode, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = $errorCode->getMessage();
            $this->errMsgIsFromErrorCode = true;
        }

        parent::__construct($message, $errorCode->value, $previous);
    }

    public function getHttpCode(): int
    {
        return $this->errorCode->getHttpCode();
    }

    public function getErrorCode(): ErrorCodeInterface
    {
        return $this->errorCode;
    }
}
