<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Profiler;

use Psr\Container\ContainerInterface;

class EventHandler implements EventInterface
{
    protected int $offset = 0;

    /**
     * @param class-string<EventInterface>[] $handlers
     */
    public function __construct(private readonly ContainerInterface $container, private readonly array $handlers)
    {
    }

    public function handle(array $event): array
    {
        if ($this->offset) {
            return $event;
        }
        foreach ($this->handlers as $handler) {
            $event = $this->container->get($handler)->handle($event);
            $this->next();
        }
        return $event;
    }

    protected function next(): void
    {
        ++$this->offset;
    }
}
