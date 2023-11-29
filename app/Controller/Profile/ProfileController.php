<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Controller\Profile;

use App\Controller\AbstractController;
use App\Middleware\Auth\AuthMiddleware;
use App\Service\ProfileService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/profile')]
#[Middleware(middleware: AuthMiddleware::class)]
final class ProfileController extends AbstractController
{
    #[Inject]
    protected ProfileService $profileService;

    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface
    {
        return $this->response->success(3);
    }

    #[GetMapping(path: 'get')]
    public function get(RequestInterface $request): ResponseInterface
    {
        $data = $this->profileService->info((int) $request->input('id'));
        return $this->response->success($data);
    }

    #[GetMapping(path: 'flame')]
    public function flame(RequestInterface $request): ResponseInterface
    {
        $data = $this->profileService->flame((int) $request->input('id'));
        return $this->response->success($data);
    }
}
