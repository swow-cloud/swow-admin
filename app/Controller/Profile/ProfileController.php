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
use App\Service\ProfileService;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function file_put_contents;

#[Controller(prefix: '/profile')]
class ProfileController extends AbstractController
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
        return $this->response->success(2);
    }

    #[GetMapping(path: 'flame')]
    public function flame(RequestInterface $request): ResponseInterface
    {
        $data = $this->profileService->flame((int) $request->input('id'));
        file_put_contents(__DIR__ . '/1.json', Json::encode($data['wt']));
        return $this->response->success($data);
    }
}
