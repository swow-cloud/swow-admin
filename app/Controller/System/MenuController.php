<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Controller\System;

use App\Controller\AbstractController;
use App\Logic\System\MenuLogic;
use App\Middleware\Auth\AuthMiddleware;
use App\Request\System\MenuRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/system/menu')]
#[Middleware(AuthMiddleware::class)]
class MenuController extends AbstractController
{
    #[Inject]
    public MenuLogic $menuLogic;

    #[GetMapping(path: 'tree')]
    public function tree(): ResponseInterface
    {
        return $this->response->success($this->menuLogic->treeMenu());
    }

    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface
    {
        return $this->response->success($this->menuLogic->list($this->request->all()));
    }

    #[PostMapping(path: 'add')]
    #[Scene(scene: 'add')]
    public function add(MenuRequest $request): ResponseInterface
    {
        $id = $this->menuLogic->add($request->all());
        return $this->response->success(['id' => $id]);
    }

    #[PostMapping(path: 'update')]
    #[Scene(scene: 'update')]
    public function update(MenuRequest $request): ResponseInterface
    {
        $id = $this->menuLogic->update($request->all());
        return $this->response->success(['id' => $id]);
    }

    #[PostMapping(path: 'delete')]
    public function delete(): ResponseInterface
    {
        return $this->response->success([]);
    }
}
