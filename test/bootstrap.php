<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\ClassLoader;

/**
 * This file is part of Cloud-Admin project.
 *
 * @see     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
\error_reporting(E_ALL);
\date_default_timezone_set('Asia/Shanghai');

! \defined('BASE_PATH') && \define('BASE_PATH', \dirname(__DIR__, 1));
! \defined('SWOOLE_HOOK_FLAGS') && \define('SWOOLE_HOOK_FLAGS', 0);

require BASE_PATH . '/vendor/autoload.php';

ClassLoader::init();

$container = require BASE_PATH . '/config/container.php';

$container->get(ApplicationInterface::class);
