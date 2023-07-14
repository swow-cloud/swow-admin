<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Logic;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\SmsService;
use Hyperf\Di\Annotation\Inject;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RedisException;

use function CloudAdmin\Utils\redisClient;

class SmsLogic
{
    #[Inject]
    public SmsService $smsService;

    #[ArrayShape(['id' => 'string', 'phone' => 'string', 'verify_code' => 'string', 'send_time' => 'string'])]
    public function send(string $phone): array
    {
        $result = $this->smsService->send($phone);
        if ($result['verify_code']) {
            try {
                // todo 需要验证短信验证码是否一直重复提交2023-07-14
                if (redisClient()->setex(
                    sprintf('verifyCode-%s', $result['phone']),
                    5 * 60,
                    $result['verify_code']
                )) {
                    return $result;
                }
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface|RedisException $e) {
            }
        }
        throw new BusinessException(ErrorCode::SERVER_ERROR);
    }
}
