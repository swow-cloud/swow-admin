<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Casbin\Listener;

use Casbin\Enforcer;
use CloudAdmin\Casbin\Event\PolicyChanged;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class OnPolicyChangedListener implements ListenerInterface
{
    public function __construct(public ContainerInterface $container)
    {
    }

    /**
     * @return string[]
     */
    public function listen(): array
    {
        return [PolicyChanged::class];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function process(object $event): void
    {
        if ($event instanceof PolicyChanged) {
            $this->container->get(Enforcer::class)->loadPolicy();
        }
    }
}
