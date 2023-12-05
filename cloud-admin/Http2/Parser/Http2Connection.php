<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Http2\Parser;

use Swow\Psr7\Server\ServerConnection;

final class Http2Connection
{
    private int $expirationTime = 0;

    public function __construct(public ServerConnection $connection)
    {
    }

    public function getRemoteAddress(): string
    {
        return $this->connection->getPeerAddress();
    }

    public function getLocalAddress(): string
    {
        return $this->connection->getSockAddress();
    }

    public function getPort(): int
    {
        return $this->connection->getPeerPort();
    }

    public function getExpirationTime(): int
    {
        return $this->expirationTime;
    }

    public function updateExpirationTime(int $param): void
    {
        $this->expirationTime = $param;
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
