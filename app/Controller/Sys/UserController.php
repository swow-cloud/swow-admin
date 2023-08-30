<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Controller\Sys;

use App\Controller\AbstractController;
use App\Logic\UserLogic;
use App\Middleware\Auth\AuthMiddleware;
use App\Model\User;
use App\Request\UserRequest;
use Carbon\Carbon;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

use function CloudAdmin\Utils\logger;
use function sprintf;

#[Controller(prefix: 'sys/user')]
class UserController extends AbstractController
{
    #[Inject]
    public UserLogic $userLogic;

    #[PostMapping(path: 'register')]
    public function register()
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[PostMapping(path: 'signIn')]
    public function login(UserRequest $request): ResponseInterface
    {
        $token = $this->userLogic->login(
            $request->input('username'),
            $request->input('password')
        );

        return $this->response->success($token);
    }

    #[PostMapping(path: 'signOut')]
    #[Middleware(middleware: AuthMiddleware::class)]
    public function logout(RequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = Context::get('user');
        try {
            $isLogout = $this->userLogic->logout(JWTUtil::getToken($request));
            if ($isLogout) {
                logger()->error(sprintf('用户[%s]:[%s]退出登录', $user->id, Carbon::now()->toDateTimeString()));
                return $this->response->success([], '退出成功!');
            }
            return $this->response->fail('退出失败,请稍候再试!');
        } catch (Throwable $throwable) {
            /* @noinspection PhpUnhandledExceptionInspection */
            logger()->error(sprintf('用户[%s]退出失败,原因:[%s]', $user->id, $throwable->getMessage()));
            return $this->response->fail('退出失败,请稍候再试!');
        }
    }
}
