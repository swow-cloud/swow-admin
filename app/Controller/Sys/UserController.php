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
use App\Exception\BusinessException;
use App\Logic\UserLogic;
use App\Middleware\Auth\AuthMiddleware;
use App\Request\UserRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;

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
        try {
            $token = $this->userLogic->login(
                $request->input('username'),
                $request->input('password')
            );

            return $this->response->success($token);
        } catch (BusinessException $e) {
            return $this->response->fail($e);
        }
    }

    #[PostMapping(path: 'signOut')]
    #[Middleware(middleware: AuthMiddleware::class)]
    public function signOut()
    {
    }
}
