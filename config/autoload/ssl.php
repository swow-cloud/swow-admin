<?php

declare(strict_types=1);

use function Hyperf\Support\env;
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
return [
    'enable' => env('ENABLE_SSL', false),
    'certificate' => env('CERTIFICATE'),
    'certificate_key' => env('CERTIFICATE_KEY'),
    'verify_peer' => false,
    'verify_peer_name' => false,
    'alpn_protocols' => 'h2',
    'allow_self_signed' => true,
];
