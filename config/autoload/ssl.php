<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
return [
    'enable' => \Hyperf\Support\env('ENABLE_SSL', false),
    'certificate' => \Hyperf\Support\env('CERTIFICATE'),
    'certificate_key' => \Hyperf\Support\env('CERTIFICATE_KEY'),
    'verify_peer' => false,
    'verify_peer_name' => false,
    'alpn_protocols' => 'h2',
    'allow_self_signed' => true,
];
