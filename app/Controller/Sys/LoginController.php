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
use Hyperf\HttpServer\Annotation\PostMapping;

class LoginController extends AbstractController
{
    #[PostMapping]
    public function register()
    {
    }

    #[PostMapping]
    public function login()
    {
    }
}
