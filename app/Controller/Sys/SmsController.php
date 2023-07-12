<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Controller\Sys;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Kernel\Http\Response;
use App\Logic\SmsLogic;
use App\Request\SmsRequest;
use Exception;
use Hyperf\Constants\Exception\ConstantsException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

use function CloudAdmin\Utils\di;

#[Controller(prefix: 'sys/sms')]
class SmsController extends AbstractController
{
    #[Inject]
    public SmsLogic $smsLogic;

    #[PostMapping(path: 'get-sms-verify-code')]
    #[RateLimit(create: 1, consume: 1, capacity: 1, limitCallback: [
        SmsController::class,
        'limitCallback',
    ], key: [SmsController::class, 'getKey'])]
    public function getSmsVerifyCode(SmsRequest $request): ResponseInterface
    {
        try {
            if ($ret = $this->smsLogic->send($request->input('phone'))) {
                return $this->response->success($ret);
            }
        } catch (Exception $exception) {
            return $this->response->fail($exception->getCode());
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getKey(ProceedingJoinPoint $proceedingJoinPoint): ?string
    {
        return di()->get(RequestInterface::class)->input('phone');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ConstantsException
     */
    public static function limitCallback(
        float $seconds,
        ProceedingJoinPoint $proceedingJoinPoint
    ): ResponseInterface {
        return di()->get(Response::class)->fail(ErrorCode::SMS_EXCEEDING_THE_CURRENT_LIMIT_ERROR);
    }
}
