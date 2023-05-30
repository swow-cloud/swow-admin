<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Controller;

use function CloudAdmin\Utils\logger;
use function CloudAdmin\Utils\stdout;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
        logger()->error('test error');
        logger()->info('test info');
        logger()->debug('test debug');
        stdout()->info('1111222');

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}
