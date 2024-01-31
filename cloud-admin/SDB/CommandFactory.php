<?php
declare(strict_types=1);
namespace CloudAdmin\SDB;

use CloudAdmin\SDB\Command\HandlerInterface;
use Psr\Container\ContainerInterface;

class CommandFactory
{
    public function __construct(
        protected readonly ContainerInterface $container,
    ) {}

    public function get(string $name): HandlerInterface
    {

    }
}