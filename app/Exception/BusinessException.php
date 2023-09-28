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

class BusinessException extends ServerException
{
    public bool $errMsgIsFromErrorCode = false;

    protected ErrorCodeInterface $errorCode;

    public function __construct(ErrorCodeInterface $code, string $message = null, Throwable $previous = null)
    {
        $this->errorCode = $code;

        if (is_null($message)) {
            $message = $code->getMessage();
            $this->errMsgIsFromErrorCode = true;
        }

        parent::__construct($message, $code->value, $previous);
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
