<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace Hyperf\Engine\Http;

use CloudAdmin\Server\SslConfig;
use Hyperf\Engine\Contract\Http\ServerInterface;
use Hyperf\Engine\Coroutine;
use Hyperf\Process\ProcessManager;
use Psr\Log\LoggerInterface;
use Swow\CoroutineException;
use Swow\Errno;
use Swow\Extension;
use Swow\Http\Protocol\ProtocolException as HttpProtocolException;
use Swow\Psr7\Psr7;
use Swow\Psr7\Server\Server as Psr7Server;
use Swow\Socket;
use Swow\SocketException;
use Throwable;

use function Hyperf\Config\config;
use function in_array;
use function sleep;

final class Server extends Psr7Server implements ServerInterface
{
    public ?string $host = null;

    public ?int $port = null;

    protected bool $ssl = false;

    protected ?SslConfig $sslConfig = null;

    /**
     * @var callable
     */
    protected $handler;

    public function __construct(protected LoggerInterface $logger)
    {
        parent::__construct();

        /** @var array{certificate:string,certificate_key:string,verify_peer:bool,verify_peer_name:bool,allow_self_signed:bool} $config */
        $config = config('ssl');

        if ($config['enable'] ?? false) {
            if (! Extension::isBuiltWith('ssl')) {
                exit('ssl配置项未开启!');
            }

            $this->ssl = true;
            unset($config['enable']);
            $this->sslConfig = new SslConfig($config);
        }
    }

    /**
     * @return $this
     */
    public function bind(
        string $name,
        int $port = 0,
        int $flags = Socket::BIND_FLAG_NONE,
    ): static {
        $this->host = $name;
        $this->port = $port;
        parent::bind($name, $port, $flags);
        return $this;
    }

    /**
     * @return $this
     */
    public function handle(callable $callable): static
    {
        $this->handler = $callable;
        return $this;
    }

    /** @noinspection DuplicatedCode */
    public function start(): void
    {
        $this->listen();
        $options = null;

        if ($this->ssl) {
            $options = $this->sslConfig->toArray();
        }

        // 多个 server 自行在外层处理协程与 waitAll
        while (ProcessManager::isRunning()) {
            try {
                $connection = $this->acceptConnection();

                if ($options !== null) {
                    $connection->enableCrypto($options);
                }

                Coroutine::create(function () use ($connection) {
                    try {
                        while (true) {
                            $request = null;

                            try {
                                $request = $connection->recvHttpRequest();
                                $handler = $this->handler;
                                $handler($request, $connection);
                            } catch (HttpProtocolException $exception) {
                                $connection->error(
                                    $exception->getCode(),
                                    $exception->getMessage(),
                                );
                            }

                            if (! $request || ! Psr7::detectShouldKeepAlive($request)) {
                                break;
                            }
                        }
                    } catch (Throwable $exception) {
                        $this->logger->critical((string) $exception);
                    } finally {
                        $connection->close();
                    }
                });
            } catch (CoroutineException|SocketException $exception) {
                if (in_array(
                    $exception->getCode(),
                    [Errno::EMFILE, Errno::ENFILE, Errno::ENOMEM],
                    true,
                )) {
                    $this->logger->warning('Socket resources have been exhausted.');
                    sleep(1);
                } elseif ($exception->getCode() === Errno::ECANCELED) {
                    $this->logger->info('Socket accept has been canceled.');
                    break;
                } else {
                    $this->logger->error((string) $exception);
                    break;
                }
            } catch (Throwable $exception) {
                $this->logger->error((string) $exception);
            }
        }
    }

    public function shutdown(): void
    {
        $this->close();
    }
}
