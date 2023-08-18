<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\SDB\Debugger;

use Swow\Socket;

/**
 * Class ServerConfig.
 *
 * Represents the configuration for a server.
 */
class ServerConfig
{
    final public function __construct(
        public string $host,
        public int $port,
        public int $backLog = Socket::DEFAULT_BACKLOG
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getBackLog(): int
    {
        return $this->backLog;
    }

    /**
     * Convert the object to an array.
     * @phpstan-return array{'host':string,'port':int,'backLog':int}
     * @return array the object converted to an array
     */
    public function toArray(): array
    {
        return [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'backLog' => $this->getBackLog(),
        ];
    }
}
