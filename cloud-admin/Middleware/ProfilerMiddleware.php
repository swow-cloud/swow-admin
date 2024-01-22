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
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function CloudAdmin\Utils\ip;
use function explode;
use function Hyperf\Coroutine\defer;
use function Hyperf\Support\env;
use function microtime;
use function xhprof_disable;
use function xhprof_enable;

final class ProfilerMiddleware implements MiddlewareInterface
{
    /**
     * @phpstan-param ContainerInterface $container
     * @phpstan-param ConfigInterface $config
     */
    public function __construct(protected readonly ContainerInterface $container, protected readonly ConfigInterface $config) {}

    /**
     * @phpstan-param  ServerRequestInterface $request
     * @phpstan-param  RequestHandlerInterface $handler
     * @phpstan-return  ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $time = microtime();
        if ($this->enable()) {
            xhprof_enable($this->config->get('profiler.options.flags'));
            defer(function () use ($time, $request) {
                try {
                    $this->logAndSave($time, $request, Context::get(ResponseInterface::class));
                } catch (Throwable) {
                    // todo: not here
                }
            });
        }
        $response = $handler->handle($request);
        Context::set(ResponseInterface::class, $response);
        return $response;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @phpstan-param  string $startTime
     * @phpstan-param  ServerRequestInterface|null $request
     * @phpstan-param  ResponseInterface|null $response
     * @phpstan-return  bool
     */
    private function logAndSave(string $startTime, ?ServerRequestInterface $request, ?ResponseInterface $response): bool
    {
        $times = explode(' ', $startTime);
        $monitor = new Monitor();
        $monitor->request_url = $request->getUri()->getPath();
        $monitor->app_name = env('APP_NAME');
        $monitor->request_body = Json::encode($this->container->get(Request::class)->all());
        $monitor->request_time = $times[1];
        $monitor->request_time_micro = $times[0] * 1_000_000;
        $monitor->type = $request->getMethod();
        $monitor->request_ip = ip($request);
        $profiler = xhprof_disable();
        $monitor->profile = Json::encode($profiler);
        $monitor->mu = $profiler['main()']['mu'];
        $monitor->pmu = $profiler['main()']['pmu'];
        $monitor->ct = $profiler['main()']['ct'];
        $monitor->cpu = $profiler['main()']['cpu'];
        $monitor->wt = $profiler['main()']['wt'];

        $monitor->response = (string) $response->getBody();
        return $monitor->save();
    }

    private function enable()
    {
        return $this->config->get('profiler.enable') ?? false;
    }
}
