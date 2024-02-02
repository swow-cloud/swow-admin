<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\SDB\Business;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Devtool\Describe\RoutesCommand;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\TableSeparator;

use function array_merge;
use function array_slice;
use function count;
use function implode;
use function is_array;
use function is_null;
use function is_string;

/**
 * @see  RoutesCommand
 */
final class Route
{
    /**
     * @phpstan-param ContainerInterface $container
     * @phpstan-param ConfigInterface $config
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfigInterface $config,
    ) {}

    /**
     * @phpstan-return array<array-key, mixed>
     */
    public function getRoute(): array
    {
        $factory = $this->container->get(DispatcherFactory::class);
        $router = $factory->getRouter('http');
        $data = $this->analyzeRouter($router);
        $rows = [];

        foreach ($data as $route) {
            $route['method'] = implode('|', $route['method']);
            $rows[] = $route;
            $rows[] = new TableSeparator();
        }

        return array_slice($rows, 0, count($rows) - 1);
    }

    /**
     * @phpstan-param string $server
     * @phpstan-param RouteCollector $router
     * @phpstan-param string|null $path
     * @phpstan-return array<array-key, mixed>
     */
    private function analyzeRouter(
        RouteCollector $router,
    ): array {
        $data = [];
        [$staticRouters, $variableRouters] = $router->getData();

        foreach ($staticRouters as $method => $items) {
            foreach ($items as $handler) {
                $this->analyzeHandler($data, $method, $handler);
            }
        }

        foreach ($variableRouters as $method => $items) {
            foreach ($items as $item) {
                if (is_array($item['routeMap'] ?? false)) {
                    foreach ($item['routeMap'] as $routeMap) {
                        $this->analyzeHandler(
                            $data,
                            $method,
                            $routeMap[0],
                        );
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @phpstan-param array<array-key, mixed> &$data
     * @phpstan-param string $serverName
     * @phpstan-param string $method
     * @phpstan-param string|null $path
     * @phpstan-param Handler $handler
     * @phpstan-return array<array-key, mixed>
     */
    private function analyzeHandler(
        array &$data,
        string $method,
        Handler $handler,
    ): void {
        $uri = $handler->route;
        if (! is_null(null) && ! Str::contains($uri, null)) {
            return;
        }
        if (is_array($handler->callback)) {
            $action = $handler->callback[0] . '::' . $handler->callback[1];
        } elseif (is_string($handler->callback)) {
            $action = $handler->callback;
        } else {
            $action = 'Closure';
        }
        $unique = "http|{$uri}|{$action}";
        if (isset($data[$unique])) {
            $data[$unique]['method'][] = $method;
        } else {
            // method,uri,name,action,middleware
            $registeredMiddlewares = MiddlewareManager::get('http', $uri, $method);
            $middlewares = $this->config->get('middlewares.http', []);

            $middlewares = array_merge($middlewares, $registeredMiddlewares);
            $middlewares = MiddlewareManager::sortMiddlewares($middlewares);

            $data[$unique] = [
                'server' => 'http',
                'method' => [$method],
                'uri' => $uri,
                'action' => $action,
                'middleware' => implode(PHP_EOL, $middlewares),
            ];
        }
    }
}
