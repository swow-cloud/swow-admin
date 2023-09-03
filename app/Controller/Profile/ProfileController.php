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
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/profiler')]
class ProfileController extends AbstractController
{
    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface
    {
    }
    #[GetMapping(path: 'get')]
    public function get(int $id): ResponseInterface
    {
    }
    #[GetMapping(path: 'flame')]
    public function flame(int $id):ResponseInterface{

    }
}
