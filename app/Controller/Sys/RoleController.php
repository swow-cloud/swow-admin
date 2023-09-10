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
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/sys/role')]
#[Middleware(middleware: AuthMiddleware::class)]
class RoleController extends AbstractController
{
    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface {}

    #[PostMapping(path: 'store')]
    public function store(): ResponseInterface {}

    #[PostMapping(path: 'update')]
    public function update(): ResponseInterface {}

    #[PostMapping(path: 'delete')]
    public function delete(): ResponseInterface {}
}
