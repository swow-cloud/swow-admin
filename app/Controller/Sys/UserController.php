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
use App\Request\UserRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;
use Throwable;

#[Controller(prefix: 'sys/user')]
class UserController extends AbstractController
{
    #[PostMapping(path: 'register')]
    public function register()
    {
    }

    #[PostMapping(path: 'signIn')]
    public function login(UserRequest $request): ResponseInterface
    {
        try {
            $this->request->input('username');
            $this->request->input('password');
            return $this->response->success();
        } catch (Throwable $e) {
        }
        return $this->response->fail();
    }

    #[PostMapping(path: 'signOut')]
    public function signOut()
    {
    }
}
