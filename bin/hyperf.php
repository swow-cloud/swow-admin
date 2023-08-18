#!/usr/bin/env php
<?php
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\SDB\Debugger\ServerConfig;
use CloudAdmin\SDB\Debugger\SslConfig;
use CloudAdmin\SDB\WebSocketDebugger;
use Swow\Coroutine;

function initialize(): void
{
    ini_set('display_errors', 'on');
    ini_set('display_startup_errors', 'on');

    error_reporting(E_ALL);

    date_default_timezone_set('Asia/Shanghai');

    defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__, 1));

    require BASE_PATH . '/vendor/autoload.php';
}

initialize();

(static function (): void {
    Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());

    /** @var Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';

    /** @var Symfony\Component\Console\Application $application */
    $application = $container->get(Hyperf\Contract\ApplicationInterface::class);

    $debuggerOptions = \Hyperf\Support\env('APP_DEBUG') ? \Hyperf\Config\config('debugger') : null;

    if ($debuggerOptions) {
        [$serverOptions, $sslOptions] = array_values($debuggerOptions);

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
    }

    $application->run();
})();
