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

use CloudAdmin\Annotation\EnumMessage;
use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\EnumConstantsTrait;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use ReflectionClass;

#[Constants]
enum ErrorCode: int implements ErrorCodeInterface
{
    use EnumConstantsTrait;

    #[EnumMessage('Server Error')]
    case SERVER_ERROR = 500;

    #[EnumMessage('Captcha request limit exceeded')]
    case SMS_EXCEEDING_THE_CURRENT_LIMIT_ERROR = 1010;

    #[EnumMessage('Synchronization of SMS logs failed')]
    case SMS_FAILED_TO_SYNC_SMS_LOGS = 1011;

    public function getMessage(array $translate = null): string
    {
        $reflection = new ReflectionClass($this);
        $reflection = $reflection->getReflectionConstant($this->name);
        $attributes = $reflection->getAttributes(EnumMessage::class);
        if (empty($attributes)) {
            return $this->name;
        }
        try {
            $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);
            $key = sprintf('enums.%s.%s', __CLASS__, $attributes[0]->newInstance()->message);
            $result = $translator->trans($key);

            return $key === $result ? $attributes[0]->newInstance()->message : $result;
        } catch (\Throwable) {
            return $attributes[0]->newInstance()->message;
        }
    }
}
