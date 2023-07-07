<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Constants;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\EnumConstantsTrait;
use Hyperf\Constants\Exception\ConstantsException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

#[Constants]
enum ErrorCode: int implements ErrorCodeInterface
{
    use EnumConstantsTrait;

    /**
     * @Message("Server Error")
     */
    case SERVER_ERROR = 500;

    /**
     * @Message("Captcha request limit exceeded")
     */
    case SMS_EXCEEDING_THE_CURRENT_LIMIT_ERROR = 1010;

    /**
     * @Message("Synchronization of SMS logs failed")
     */
    case SMS_FAILED_TO_SYNC_SMS_LOGS = 1011;

    /**
     * @throws ConstantsException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMessage(array $translate = null): string
    {
        $arguments = [];
        if ($translate) {
            $arguments = [$translate];
        }

        return $this->__call('getMessage', $arguments);
    }
}
