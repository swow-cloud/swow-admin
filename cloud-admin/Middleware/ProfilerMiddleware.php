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

use App\Model\Monitor;
use CloudAdmin\Profiler\EventHandler as ProfilerEventHandler;
use CloudAdmin\Profiler\Handlers\CalculateDiffsBetweenEdges;
use CloudAdmin\Profiler\Handlers\CleanupEvent;
use CloudAdmin\Profiler\Handlers\PrepareEdges;
use CloudAdmin\Profiler\Handlers\PreparePeaks;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Request;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function CloudAdmin\Utils\ip;
use function explode;
use function microtime;
use function xhprof_disable;
use function xhprof_enable;

class ProfilerMiddleware implements MiddlewareInterface
{
    public function __construct(protected readonly ContainerInterface $container, protected readonly ConfigInterface $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $time = microtime();
        if ($this->enable()) {
            xhprof_enable($this->config->get('profiler.options.flags'));
        }
        \Hyperf\Coroutine\defer(function () use ($time, $request) {
            try {
                $this->logAndSave($time, $request, Context::get(ResponseInterface::class));
            } catch (Throwable $throwable) {
                // todo: not here
            }
        });
        $response = $handler->handle($request);
        Context::set(ResponseInterface::class, $response);
        return $response;
    }

    protected function logAndSave(string $startTime, ?ServerRequestInterface $request, ?ResponseInterface $response): bool
    {
        $times = explode(' ', (string) $startTime);
        $monitor = new Monitor();
        $monitor->request_url = $request->getUri()->getPath();
        $monitor->app_name = \Hyperf\Support\env('APP_NAME');
        $monitor->request_body = Json::encode($this->container->get(Request::class)->all());
        $monitor->request_time = $times[1];
        $monitor->request_time_micro = $times[0] * 1000000;
        $monitor->type = $request->getMethod();
        $monitor->request_ip = ip($request);
        // TODO: 是否格式化profiler
        //            $eventHandler = \Hyperf\Support\make(ProfilerEventHandler::class, [$this->container, [
        //                PreparePeaks::class,
        //                CalculateDiffsBetweenEdges::class,
        //                PrepareEdges::class,
        //                CleanupEvent::class,
        //            ]]);
        $profiler = xhprof_disable();
        //            $data = $eventHandler->handle($profiler);
        $monitor->profile = Json::encode($profiler);
        $monitor->mu = $profiler['main()']['mu'];
        $monitor->pmu = $profiler['main()']['pmu'];
        $monitor->ct = $profiler['main()']['ct'];
        $monitor->cpu = $profiler['main()']['cpu'];
        $monitor->wt = $profiler['main()']['wt'];

        $monitor->response = (string) $response->getBody();
        return $monitor->save();
    }

    protected function enable()
    {
        return $this->config->get('profiler.enable') ?? false;
    }
}
