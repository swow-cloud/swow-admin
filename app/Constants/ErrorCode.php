<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Constants;

use CloudAdmin\Annotation\EnumMessage;
use CloudAdmin\Annotation\HttpCode;
use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\EnumConstantsTrait;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use ReflectionClass;
use Swow\Http\Status;
use Throwable;

use function sprintf;

#[Constants]
enum ErrorCode: int implements ErrorCodeInterface
{
    use EnumConstantsTrait;

    #[EnumMessage('Token authentication does not pass')]
    #[HttpCode(Status::UNAUTHORIZED)]
    case UNAUTHORIZED = 401;

    #[EnumMessage('Server Error')]
    #[HttpCode(Status::INTERNAL_SERVER_ERROR)]
    case SERVER_ERROR = 500;

    #[EnumMessage('Captcha request limit exceeded')]
    case SMS_EXCEEDING_THE_CURRENT_LIMIT_ERROR = 1010;

    #[EnumMessage('Synchronization of SMS logs failed')]
    case SMS_FAILED_TO_SYNC_SMS_LOGS = 1011;

    #[EnumMessage('User does not exist or is not enabled')]
    #[HttpCode(Status::UNAUTHORIZED)]
    case USER_LOGIN_ERR = 2010;

    #[EnumMessage('用户名或密码错误')]
    #[HttpCode(Status::UNAUTHORIZED)]
    case USER_LOGIN_PASSWORD_ERR = 2011;

    #[EnumMessage('资源不存在!')]
    #[HttpCode(Status::NOT_FOUND)]
    case NOT_FOUND = 404;

    public function getMessage(array $translate = null): string
    {
        return $this->getReflectedAttribute(EnumMessage::class, 'message');
    }

    public function getHttpCode(array $translate = null): int
    {
        return $this->getReflectedAttribute(HttpCode::class, 'code');
    }

    private function getReflectedAttribute(string $class, string $property): int|string
    {
        $reflection = new ReflectionClass($this);
        $reflection = $reflection->getReflectionConstant($this->name);
        $attributes = $reflection->getAttributes($class);

        if (empty($attributes)) {
            return $this->name;
        }

        try {
            $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);
            $key = sprintf('enums.%s.%s', self::class, $attributes[0]->newInstance()->{$property});
            $result = $translator->trans($key);

            return $key === $result ? $attributes[0]->newInstance()->{$property} : $result;
        } catch (Throwable) {
            return $attributes[0]->newInstance()->{$property};
        }
    }
}
