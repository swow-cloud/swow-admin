#!/usr/bin/env php
<?php
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\SDB\Debugger\ServerConfig;
use CloudAdmin\SDB\WebSocketDebugger;
use Swow\Coroutine;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', 0);

require BASE_PATH . '/vendor/autoload.php';

// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(static function () {
    Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
    /** @var Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';
    /** @var Symfony\Component\Console\Application $application */
    $application = $container->get(Hyperf\Contract\ApplicationInterface::class);
    if (\Hyperf\Support\env('APP_DEBUG')) {
        $serverConfig = new ServerConfig(host: '127.0.0.1', port: 9764);
        $debugger = WebSocketDebugger::runOnWebSocket('sdb', $serverConfig);
        Coroutine::run(function () use ($debugger) {
            $debugger->start();
        });
        $debugger->out('[Info]    Press Ctrl+C to stop the server...');
    }
    $application->run();
})();
