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

use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'sys/user')]
class UserController extends AbstractController
{
    #[PostMapping(path: 'register')]
    public function register()
    {
    }

    #[PostMapping(path: 'signIn')]
    public function login()
    {
    }

    #[PostMapping(path: 'signOut')]
    public function signOut()
    {
    }
}
