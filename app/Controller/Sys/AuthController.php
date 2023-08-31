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
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\Codec\Json;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/sys/auth')]
class AuthController extends AbstractController
{
    #[GetMapping('buttons')]
    #[Middleware(AuthMiddleware::class)]
    public function buttons(): ResponseInterface
    {
        return $this->response->success(Json::decode(' {
        "useProTable": [
            "add",
            "batchAdd",
            "export",
            "batchDelete",
            "status"
        ],
        "authButton": [
            "add",
            "edit",
            "delete",
            "import",
            "export"
        ]
    }'));
    }
}
