<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
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
use function sprintf;

class SmsLogic
{
    private const CACHE_KEY_FORMAT = 'verifyCode-%s';

    private const CACHE_EXPIRATION_TIME = 5 * 60;

    #[Inject]
    public SmsService $smsService;

    /**
     * Sends a verification code to the specified phone number.
     *
     * @param string $phone the phone number to send the verification code to
     *
     * @return array the response from the SMS service
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RedisException
     */
    #[ArrayShape(['id' => 'string', 'phone' => 'string', 'verify_code' => 'string', 'send_time' => 'string'])]
    public function send(string $phone): array
    {
        if ($code = $this->getFromCache($phone)) {
            return ['verify_code' => $code];
        }

        $result = $this->smsService->send($phone);

        if (isset($result['verify_code'])) {
            $this->saveToCache($result['phone'], $result['verify_code']);
            return $result;
        }

        throw new BusinessException(ErrorCode::SERVER_ERROR);
    }

    /**
     * Retrieves the value from the cache based on the specified phone number.
     *
     * @param string $phone the phone number to retrieve from the cache
     *
     * @return null|string the value associated with the phone number from the cache,
     *                     or null if the phone number is not found in the cache
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RedisException
     */
    private function getFromCache(string $phone): ?string
    {
        return redisClient()->get(sprintf(self::CACHE_KEY_FORMAT, $phone)) ?: null;
    }

    /**
     * Saves the verify code to the cache.
     *
     * @param string $phone the phone number
     * @param string $verifyCode the verify code to be saved
     *
     * @throws BusinessException if an error occurs while saving to cache
     */
    private function saveToCache(string $phone, string $verifyCode): void
    {
        try {
            if (! redisClient()->setex(sprintf(self::CACHE_KEY_FORMAT, $phone), self::CACHE_EXPIRATION_TIME, $verifyCode)) {
                throw new BusinessException(ErrorCode::SERVER_ERROR);
            }
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface|RedisException $e) {
            throw new BusinessException(ErrorCode::SERVER_ERROR);
        }
    }
}
