<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Test\Cases;

use CloudAdmin\Context\Coroutine;
use CloudAdmin\Log\AppendRequestIdWithMemoryUsageProcessor;
use CloudAdmin\Test\HttpTestCase;
use Hyperf\Context\Context;
use Hyperf\Di\Definition\FactoryDefinition;
use Hyperf\Di\Resolver\FactoryResolver;
use Hyperf\Di\Resolver\ResolverDispatcher;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Reflection\ClassInvoker;
use Mockery;
use Psr\Container\ContainerInterface;
use Throwable;

use function CloudAdmin\Utils\di;
use function uniqid;

/**
 * @internal
 * @coversNothing
 */
final class ExampleTest extends HttpTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);

        $res = $this->get('/');

        $this->assertSame(0, $res['code']);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('GET', $res['data']['method']);
        $this->assertSame('Hyperf', $res['data']['user']);

        $res = $this->get('/', ['user' => 'limx']);

        $this->assertSame(0, $res['code']);
        $this->assertSame('limx', $res['data']['user']);

        $res = $this->post('/', [
            'user' => 'limx',
        ]);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('limx', $res['data']['user']);

        Context::set(AppendRequestIdWithMemoryUsageProcessor::REQUEST_ID, $id = uniqid());
        $pool = new Channel(1);
        di()->get(Coroutine::class)->create(function () use ($pool) {
            try {
                $all = Context::getContainer();
                $pool->push((array) $all);
            } catch (Throwable $exception) {
                $pool->push(false);
            }
        });

        $data = $pool->pop();
        $this->assertIsArray($data);
        $this->assertSame($id, $data[AppendRequestIdWithMemoryUsageProcessor::REQUEST_ID]);
    }

    public function testGetDefinitionResolver()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $dispatcher = new ClassInvoker(new ResolverDispatcher($container));
        $resolver = $dispatcher->getDefinitionResolver(Mockery::mock(FactoryDefinition::class));
        $this->assertInstanceOf(FactoryResolver::class, $resolver);
        $this->assertSame($resolver, $dispatcher->factoryResolver);

        $resolver2 = $dispatcher->getDefinitionResolver(Mockery::mock(FactoryDefinition::class));
        $this->assertInstanceOf(FactoryResolver::class, $resolver2);
        $this->assertSame($resolver2, $dispatcher->factoryResolver);
    }
}
