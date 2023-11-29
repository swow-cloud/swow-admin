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
use App\Logic\UserLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: 'system/user')]
final class UserController extends AbstractController
{
    #[Inject]
    public UserLogic $userLogic;

    #[PostMapping(path: 'list')]
    public function list(): ResponseInterface {}

    #[PostMapping(path: 'store')]
    public function store(): ResponseInterface {}

    public function update(): ResponseInterface {}

    public function delete(): ResponseInterface {}
}
