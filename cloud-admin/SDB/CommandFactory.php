<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\SDB;

use CloudAdmin\SDB\Command\HandlerInterface;
use Exception;
use Hyperf\Di\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class CommandFactory
{
    public function __construct(
        protected readonly ContainerInterface $container,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $command): HandlerInterface
    {
        $enum = CommandEnum::from($command);
        try {
            $handler = $enum->handler();
            return $this->container->get($handler);
        } catch (Exception $e) {
            throw new NotFoundException();
        }
    }
}
