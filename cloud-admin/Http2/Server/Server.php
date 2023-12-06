<?php

/** @noinspection DuplicatedCode */
declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace Hyperf\Engine\Http;

use CloudAdmin\Http2\Config\Ssl;
use CloudAdmin\Http2\Parser\Http2Connection;
use CloudAdmin\Http2\Parser\Http2Parser;
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
use function in_array;
use function sleep;

class Server extends Psr7Server implements ServerInterface
{
    public ?string $host = null;

    public ?int $port = null;
    /**
     * @var true
     */
    protected bool $ssl = false;

    protected Ssl $sslConfig;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var callable
     */
    public $onStreamData;

    /**
     * @var callable
     */
    public $onRequest;

    /**
     * @var callable
     */
    public $onWriteBody;


    public function __construct(protected LoggerInterface $logger)
    {
        parent::__construct();

        /** @var array{certificate:string,certificate_key:string,verify_peer:bool,verify_peer_name:bool,allow_self_signed:bool} $config */
        $config = \Hyperf\Config\config('ssl');

        if ($config['enable'] ?? false) {
            if (! Extension::isBuiltWith('ssl')) {
                exit('ssl配置项未开启!');
            }

            $this->ssl = true;
            unset($config['enable']);
            $this->sslConfig = new Ssl($config);
        }
    }

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

    public function handle(callable $callable): static
    {
        $this->handler = $callable;
        return $this;
    }

    public function start(): void
    {
        $this->listen();
        $options = null;

        if ($this->ssl) {
            $options = $this->sslConfig->toArray();
        }

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
                                //todo 调试htt2
//                                $request = $connection->recvHttpRequest();
                                $handler = $this->handler;
                                $client = \Hyperf\Support\make(Http2Connection::class,['connection' => $connection]);
                                $parser = new Http2Parser(
                                    $client,
                                    [],
                                    $this->onStreamData,
                                    $this->onRequest,
                                    $this->onWriteBody,
                                );
//                                $handler($request, $connection);
                                $parser->parse($connection->recvString(), $connection);
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
}
