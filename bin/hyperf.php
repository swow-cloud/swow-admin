#!/usr/bin/env php
<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\SDB\Config\ServerConfig;
use CloudAdmin\SDB\Config\SslConfig;
use CloudAdmin\SDB\WebSocketDebugger;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\ScanHandler\ProcScanHandler;
use Psr\Container\ContainerInterface;
use Swow\Coroutine;
use Swow\Debug\Debugger\Debugger;
use Symfony\Component\Console\Application;

function initialize(): void
{
    \ini_set('display_errors', 'on');
    \ini_set('display_startup_errors', 'on');

    \error_reporting(E_ALL);

    \date_default_timezone_set('Asia/Shanghai');

    \defined('BASE_PATH') || \define('BASE_PATH', \dirname(__DIR__, 1));

    require BASE_PATH . '/vendor/autoload.php';
}

\initialize();

(static function (): void {
    ClassLoader::init(handler: new ProcScanHandler());

    /** @var ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';

    /** @var Application $application */
    $application = $container->get(ApplicationInterface::class);

    $debuggerOptions = \Hyperf\Support\env('APP_DEBUG') ? \Hyperf\Config\config('debugger') : null;

    if ($debuggerOptions) {
        if ($debuggerOptions['handler'] === Debugger::class) {
            Debugger::runOnTTY();
        } else {
            [$serverOptions, $sslOptions] = \array_values($debuggerOptions['options']);

            $serverConfig = new ServerConfig(
                host: $serverOptions['host'],
                port: $serverOptions['port']
            );

            $sslConfig = new SslConfig(
                $sslOptions['enable'],
                $sslOptions['certificate'],
                $sslOptions['certificate_key'],
                $sslOptions['verify_peer'],
                $sslOptions['verify_peer_name'],
                $sslOptions['allow_self_signed']
            );

            $debugger = WebSocketDebugger::createWithWebSocket('sdb', $serverConfig, $sslConfig);

            Coroutine::run(fn () => $debugger?->start());
            Coroutine::run(fn () => $debugger?->detectActiveConnections());
        }
    }

    $application->run();
})();
