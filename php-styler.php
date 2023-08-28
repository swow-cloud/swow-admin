<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use PhpStyler\Config;
use PhpStyler\Files;
use PhpStyler\Styler;

return new Config(
    files: new Files(__DIR__ . '/cloud-admin'),
    styler: new Styler(),
);
