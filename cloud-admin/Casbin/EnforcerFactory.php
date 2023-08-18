<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Casbin;

use Casbin\Bridge\Logger\LoggerBridge;
use Casbin\Enforcer as BaseEnforcer;
use Casbin\Exceptions\CasbinException;
use Casbin\Log\Log;
use Casbin\Model\Model;
use Hyperf\Logger\LoggerFactory;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EnforcerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws CasbinException
     */
    public function __invoke(ContainerInterface $container): BaseEnforcer
    {
        $config = \Hyperf\Config\config('casbin');
        if (is_null($config)) {
            throw new InvalidArgumentException('Enforcer config is not defined.');
        }

        if ($config['log']['enabled']) {
            $logger = $container->get(LoggerFactory::class)->get();
            Log::setLogger(new LoggerBridge($logger));
        }

        $model = new Model();
        $configType = $config['model']['config_type'];
        if ($configType === 'file') {
            $model->loadModel($config['model']['config_file_path']);
        } elseif ($configType === 'text') {
            $model->loadModelFromText($config['model']['config_text']);
        }
        if (! $config['adapter']['class']) {
            throw new InvalidArgumentException('Enforcer adapter is not defined.');
        }
        $adapter = \Hyperf\Support\make($config['adapter']['class'], $config['adapter']['constructor']);
        $enforcer = new BaseEnforcer($model, $adapter, $config['log']['enabled']);
        // set watcher
        if ($config['watcher'] && $config['watcher']['enabled']) {
            $watcher = \Hyperf\Support\make($config['watcher']['class'], $config['watcher']['constructor']);
            $enforcer->setWatcher($watcher);
        }
        return $enforcer;
    }
}
