<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
return [
    'enable' => \Hyperf\Support\env('ENABLE_SSL'),
    'certificate' => BASE_PATH . '/ssl/localhost.pem',
    'certificate_key' => BASE_PATH . '/ssl/localhost-key.pem',
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true,
];
