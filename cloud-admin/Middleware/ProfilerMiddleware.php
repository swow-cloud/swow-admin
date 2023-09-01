<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Middleware;

use CloudAdmin\Profiler\EventHandler as ProfilerEventHandler;
use CloudAdmin\Profiler\Handlers\CalculateDiffsBetweenEdges;
use CloudAdmin\Profiler\Handlers\CleanupEvent;
use CloudAdmin\Profiler\Handlers\PrepareEdges;
use CloudAdmin\Profiler\Handlers\PreparePeaks;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function xhprof_disable;
use function xhprof_enable;

class ProfilerMiddleware implements MiddlewareInterface
{
    public function __construct(protected readonly ContainerInterface $container, protected readonly ConfigInterface $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            if ($this->enable()) {
                xhprof_enable($this->config->get('profiler.options.flags'));
            }
            $response = $handler->handle($request);
        } finally {
            $data['profile'] = xhprof_disable();
            $eventHandler = \Hyperf\Support\make(ProfilerEventHandler::class, [$this->container, [
                PreparePeaks::class,
                CalculateDiffsBetweenEdges::class,
                PrepareEdges::class,
                CleanupEvent::class,
            ]]);
            $data = $eventHandler->handle($data);
        }
        return $response;
    }

    protected function enable()
    {
        return $this->config->get('profiler.enable') ?? false;
    }
}
