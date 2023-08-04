<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\SDB;

use /*
 * Class ServerConfig
 *
 * This class represents the server configuration for the CloudAdmin SDB Debugger.
 */
CloudAdmin\SDB\Debugger\ServerConfig;
use /*
 * Class SslConfig
 *
 * This class represents the SSL configuration for the SDB Debugger.
 */
CloudAdmin\SDB\Debugger\SslConfig;
use /*
 * Error class used for handling errors in the application.
 *
 * @package MyApp
 */
Error;
use /*
 * Exception class representing an error in the application.
 *
 * @category  Exceptions
 * @package   YourNamespace\YourPackage
 */
Exception;
use /*
 * Hyperf\Codec\Json
 *
 * JSON Codec for encoding and decoding data.
 */
Hyperf\Codec\Json;
use /*
 * Interface ConfigInterface
 *
 * Defines the contract for accessing configuration data in Hyperf.
 */
Hyperf\Contract\ConfigInterface;
use /*
 * Class PoolFactory
 *
 * The PoolFactory class is responsible for creating and managing instances of Redis connection pools.
 */
Hyperf\Redis\Pool\PoolFactory;
use /*
 * Class ReflectionClass
 *
 * The ReflectionClass class reports information about a class.
 */
ReflectionClass;
use /*
 * Class RuntimeException
 *
 * The RuntimeException class represents a generic runtime exception.
 *
 * @package MyApp
 */
RuntimeException;
use /*
 * Class Swow\Channel
 *
 * The Swow\Channel class represents a communication channel between senders and receivers.
 *
 * Each channel has a capacity, which defines the number of messages that can be sent or
 * received before blocking the sender or receiver. If the capacity is zero, the channel
 * is considered unbuffered and each send operation must wait for a corresponding receive
 * operation and vice versa.
 *
 * @package Swow
 */
Swow\Channel;
use /*
 * Class Swow\Coroutine
 *
 * This class represents a coroutine in Swow.
 * Coroutines are used for cooperative multitasking, allowing multiple tasks to be executed concurrently
 * without the need for threads or parallel processing.
 */
Swow\Coroutine;
use /*
 * Class Swow\CoroutineException
 *
 * An exception that is thrown when an error occurs in a coroutine.
 *
 * @package Swow
 */
Swow\CoroutineException;
use /*
 * Class Debugger
 *
 * The Debugger class provides various debugging and logging functionalities.
 *
 * @package Swow\Debug\Debugger
 */
Swow\Debug\Debugger\Debugger;
use /*
 * Class DebuggerException
 *
 * An exception class used by the Debugger class in the Swow\Debug\Debugger namespace.
 *
 * @package Swow\Debug\Debugger
 */
Swow\Debug\Debugger\DebuggerException;
use /*
 * Namespace for handling system error codes.
 *
 * This namespace provides functionality for handling and working with system error codes.
 */
Swow\Errno;
use Swow\Http\Protocol\ProtocolException;
use /*
 * Class HttpStatus
 *
 * This class contains constants representing the HTTP status codes according to the HTTP/1.1 specification.
 * Each constant is named after the corresponding HTTP status code.
 *
 * @package Swow\Http
 */
Swow\Http\Status;
use /*
 * Class UpgradeType
 *
 * Represents an HTTP/1.x Upgrade type in a request or response message.
 * This class implements the Psr\Http\Message\UpgradeTypeInterface.
 */
Swow\Psr7\Message\UpgradeType;
use /*
 * The Psr7 class provides utilities for working with PSR-7 HTTP message interfaces.
 *
 * @package Swow\Psr7
 */
Swow\Psr7\Psr7;
use /*
 * Represents an HTTP server.
 *
 * This class provides a convenient way to handle HTTP requests and send HTTP responses.
 *
 * @package Swow\Psr7\Server
 */
Swow\Psr7\Server\Server;
use /*
 * Class Swow\Socket
 *
 * This class represents a socket object that provides a high-level interface for
 * creating, managing, and interacting with TCP/IP sockets.
 *
 * @package Swow\Socket
 */
Swow\Socket;
use /*
 * Class Swow\SocketException
 *
 * SocketException represents an exception that is thrown when there is an error related to sockets.
 *
 * @package Swow
 */
Swow\SocketException;
use /*
 * Provides constants for WebSocket opcodes.
 *
 * @package Swow\WebSocket
 */
Swow\WebSocket\Opcode;
use /*
 * Class WebSocket
 *
 * The WebSocket class represents a WebSocket connection used for real-time communication.
 *
 * @package Swow\WebSocket
 */
Swow\WebSocket\WebSocket;
use /*
 * Throwable is the base class for all the exceptions.
 *
 * @since PHP 5, PHP 7
 */
Throwable;
use /*
 * A WeakMap is a built-in object in JavaScript that allows you to establish
 * weak relationships between objects and values. WeakMap keys are weak
 * references, which means they don't prevent garbage collection of the object
 * they refer to. In other words, if there are no other references to the key
 * object, it may be garbage collected (removed from memory) and the WeakMap
 * will automatically remove its corresponding value.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/WeakMap
 *
 * @class WeakMap
 */
WeakMap;

use function /*
 * Class di
 *
 * The di (Dependency Injection) class provides a simple implementation of dependency injection container.
 * It allows for easy management of object dependencies and their creation.
 * The di class follows the Singleton design pattern, ensuring only one instance of the container is used.
 */
CloudAdmin\Utils\di;
use function /*
 * Dump a variable and return its output as a string.
 *
 * @param mixed $variable The variable to be dumped.
 *
 * @return string The output string of the variable dump.
 */
Swow\Debug\var_dump_return;

/**
 * Class WebSocketDebugger.
 *
 * This class represents a WebSocket debugger that extends the base Debugger class.
 * It allows debugging of coroutines using a WebSocket connection.
 */
class WebSocketDebugger extends Debugger
{
    protected ?Server $socket = null;

    protected static ServerConfig $serverConfig;

    protected static ?SslConfig $sslConfig;

    final public function __construct()
    {
        parent::__construct();
    }

    final public static function createWithWebSocket(string $keyword = 'sdb', ServerConfig $serverConfig = null, SslConfig $sslConfig = null): static
    {
        if ($serverConfig === null) {
            throw new RuntimeException('Debugger Server Not Setting...');
        }
        self::$serverConfig = $serverConfig;
        self::$sslConfig = $sslConfig;
        return static::getInstance()->run($keyword);
    }

    public function start(): void
    {
        $this->socket = new Server(Socket::TYPE_TCP);
        $this->socket->bind(self::$serverConfig->getHost(), self::$serverConfig->getPort())->listen(self::$serverConfig->getBackLog());
        $options = null;
        if (self::$sslConfig !== null) {
            $options = self::$sslConfig->toArray();
            unset($options['ssl']);
        }
        while (true) {
            try {
                $connection = null;
                $connection = $this->socket->acceptConnection();
                if ($options !== null) {
                    $connection->enableCrypto($options);
                }
                Coroutine::run(function () use ($connection): void {
                    try {
                        while (true) {
                            $request = null;
                            try {
                                $request = $connection->recvHttpRequest();
                                switch ($request->getUri()->getPath()) {
                                    case '/':
                                        $upgradeType = Psr7::detectUpgradeType($request);

                                        if (($upgradeType & UpgradeType::UPGRADE_TYPE_WEBSOCKET) === 0) {
                                            throw new ProtocolException(Status::BAD_REQUEST, 'Unsupported Upgrade Type');
                                        }
                                        $connection->upgradeToWebSocket($request);
                                        $request = null;
                                        while (true) {
                                            $frame = $connection->recvWebSocketFrame();
                                            $opcode = $frame->getOpcode();
                                            switch ($opcode) {
                                                case Opcode::PING:
                                                    $connection->send(WebSocket::PONG_FRAME);
                                                    break;
                                                case Opcode::PONG:
                                                    break;
                                                case Opcode::CLOSE:
                                                    break 4;
                                                case Opcode::TEXT:
                                                    $in = $frame->getPayloadData()->getContents();
                                                    if ($in === "\n") {
                                                        $in = $this->getLastCommand();
                                                    }
                                                    $this->setLastCommand($in);
                                                    _next:
                                                    try {
                                                        $lines = array_filter(explode("\n", $in));
                                                        foreach ($lines as $line) {
                                                            $arguments = explode(' ', $line);
                                                            foreach ($arguments as &$argument) {
                                                                $argument = trim($argument);
                                                            }
                                                            unset($argument);
                                                            $arguments = array_filter($arguments, static fn (string $value) => $value !== '');
                                                            $command = array_shift($arguments);
                                                            switch ($command) {
                                                                case 'ps':
                                                                    $this->showCoroutines(Coroutine::getAll());
                                                                    break;
                                                                case 'attach':
                                                                case 'co':
                                                                case 'coroutine':
                                                                    $id = $arguments[0] ?? 'unknown';
                                                                    if (! is_numeric($id)) {
                                                                        throw new DebuggerException('Argument[1]: Coroutine id must be numeric');
                                                                    }
                                                                    $coroutine = Coroutine::get((int) $id);
                                                                    if (! $coroutine) {
                                                                        throw new DebuggerException("Coroutine#{$id} Not found");
                                                                    }
                                                                    if ($command === 'attach') {
                                                                        $this->checkBreakPointHandler();
                                                                        if ($coroutine === Coroutine::getCurrent()) {
                                                                            throw new DebuggerException('Attach debugger is not allowed');
                                                                        }
                                                                        static::getDebugContextOfCoroutine($coroutine)->stop = true;
                                                                    }
                                                                    $this->setCurrentCoroutine($coroutine);
                                                                    $in = 'bt';
                                                                    goto _next;
                                                                case 'bt':
                                                                case 'backtrace':
                                                                    $this->showCoroutine($this->getCurrentCoroutine(), false)
                                                                        ->showSourceFileContentByTrace($this->getCurrentCoroutineTrace(), 0, true);
                                                                    break;
                                                                case 'f':
                                                                case 'frame':
                                                                    $frameIndex = $arguments[0] ?? null;
                                                                    if (! is_numeric($frameIndex)) {
                                                                        throw new DebuggerException('Frame index must be numeric');
                                                                    }
                                                                    $frameIndex = (int) $frameIndex;
                                                                    if ($this->getCurrentFrameIndex() !== $frameIndex) {
                                                                        $this->out("Switch to frame {$frameIndex}");
                                                                    }
                                                                    $this->setCurrentFrameIndex($frameIndex);
                                                                    $trace = $this->getCurrentCoroutineTrace();
                                                                    $frameIndex = $this->getCurrentFrameIndex();
                                                                    $this
                                                                        ->showTrace($trace, $frameIndex, false)
                                                                        ->showSourceFileContentByTrace($trace, $frameIndex, true);
                                                                    break;
                                                                case 'b':
                                                                case 'breakpoint':
                                                                    $breakPoint = $arguments[0] ?? '';
                                                                    if ($breakPoint === '') {
                                                                        throw new DebuggerException('Invalid break point');
                                                                    }
                                                                    $coroutine = $this->getCurrentCoroutine();
                                                                    if ($coroutine === Coroutine::getCurrent()) {
                                                                        $this
                                                                            ->out("Added global break-point <{$breakPoint}>")
                                                                            ->addBreakPoint($breakPoint);
                                                                    }
                                                                    break;
                                                                case 'n':
                                                                case 'next':
                                                                case 's':
                                                                case 'step':
                                                                case 'step_in':
                                                                case 'c':
                                                                case 'continue':
                                                                    $coroutine = $this->getCurrentCoroutine();
                                                                    $context = static::getDebugContextOfCoroutine($coroutine);
                                                                    if (! $context->stopped) {
                                                                        if ($context->stop) {
                                                                            $this->waitStoppedCoroutine($coroutine);
                                                                        } else {
                                                                            throw new DebuggerException('Not in debugging');
                                                                        }
                                                                    }
                                                                    switch ($command) {
                                                                        case 'n':
                                                                        case 'next':
                                                                        case 's':
                                                                        case 'step_in':
                                                                            if ($command === 'n' || $command === 'next') {
                                                                                $this->lastTraceDepth = $coroutine->getTraceDepth() - static::getCoroutineTraceDiffLevel($coroutine, 'nextCommand');
                                                                            }
                                                                            $coroutine->resume();
                                                                            $this->waitStoppedCoroutine($coroutine);
                                                                            $this->lastTraceDepth = PHP_INT_MAX;
                                                                            $in = 'f 0';
                                                                            goto _next;
                                                                        case 'c':
                                                                        case 'continue':
                                                                            static::getDebugContextOfCoroutine($coroutine)->stop = false;
                                                                            $this->out("Coroutine#{$coroutine->getId()} continue to run...");
                                                                            $coroutine->resume();
                                                                            break;
                                                                        default:
                                                                            throw new Error('Never here');
                                                                    }
                                                                    break;
                                                                case 'l':
                                                                case 'list':
                                                                    $lineCount = $arguments[0] ?? null;
                                                                    if ($lineCount === null) {
                                                                        $this->showFollowingSourceFileContent();
                                                                    } elseif (is_numeric($lineCount)) {
                                                                        $this->showFollowingSourceFileContent((int) $lineCount);
                                                                    } else {
                                                                        throw new DebuggerException('Argument[1]: line no must be numeric');
                                                                    }
                                                                    break;
                                                                case 'p':
                                                                case 'print':
                                                                case 'exec':
                                                                    $expression = implode(' ', $arguments);
                                                                    if (! $expression) {
                                                                        throw new DebuggerException('No expression');
                                                                    }
                                                                    if ($command === 'exec') {
                                                                        $transfer = new Channel();
                                                                        Coroutine::run(static function () use ($expression, $transfer): void {
                                                                            $transfer->push(Coroutine::getCurrent()->eval($expression));
                                                                        });
                                                                        // TODO: support ctrl + c (also support ctrl + c twice confirm on global scope?)
                                                                        $result = var_dump_return($transfer->pop());
                                                                    } else {
                                                                        $coroutine = $this->getCurrentCoroutine();
                                                                        $index = $this->getCurrentFrameIndexExtendedForExecution();
                                                                        $result = var_dump_return($coroutine->eval($expression, $index));
                                                                    }
                                                                    $this->out($result, false);
                                                                    break;
                                                                case 'vars':
                                                                    $coroutine = $this->getCurrentCoroutine();
                                                                    $index = $this->getCurrentFrameIndexExtendedForExecution();
                                                                    $result = var_dump_return($coroutine->getDefinedVars($index));
                                                                    $this->out($result, false);
                                                                    break;
                                                                case 'z':
                                                                case 'zombie':
                                                                case 'zombies':
                                                                    $time = $arguments[0] ?? null;
                                                                    if (! is_numeric($time)) {
                                                                        throw new DebuggerException('Argument[1]: Time must be numeric');
                                                                    }
                                                                    $this->out("Scanning zombie coroutines ({$time}s)...");
                                                                    $switchesMap = new WeakMap();
                                                                    foreach (Coroutine::getAll() as $coroutine) {
                                                                        $switchesMap[$coroutine] = $coroutine->getSwitches();
                                                                    }
                                                                    usleep((int) ($time * 1000 * 1000));
                                                                    $zombies = [];
                                                                    foreach ($switchesMap as $coroutine => $switches) {
                                                                        if ($coroutine->getSwitches() === $switches) {
                                                                            $zombies[] = $coroutine;
                                                                        }
                                                                    }
                                                                    $this
                                                                        ->out('Following coroutine maybe zombies:')
                                                                        ->showCoroutines($zombies);
                                                                    break;
                                                                case 'kill':
                                                                    if (count($arguments) === 0) {
                                                                        throw new DebuggerException('Required coroutine id');
                                                                    }
                                                                    foreach ($arguments as $index => $argument) {
                                                                        if (! is_numeric($argument)) {
                                                                            $this->exception("Argument[{$index}] '{$argument}' is not numeric");
                                                                        }
                                                                    }
                                                                    foreach ($arguments as $argument) {
                                                                        $coroutine = Coroutine::get((int) $argument);
                                                                        if ($coroutine) {
                                                                            $coroutine->kill();
                                                                            $this->out("Coroutine#{$argument} killed");
                                                                        } else {
                                                                            $this->exception("Coroutine#{$argument} not exists");
                                                                        }
                                                                    }
                                                                    break;
                                                                case 'killall':
                                                                    Coroutine::killAll();
                                                                    $this->out('All coroutines has been killed');
                                                                    break;
                                                                case 'clear':
                                                                    $this->clear();
                                                                    break;
                                                                case 'pool':
                                                                    $poolCmd = $arguments[0] ?? 'unknown';

                                                                    if (! is_string($poolCmd)) {
                                                                        throw new DebuggerException('Argument[1]: Coroutine id must be string,like: pool redis:default,pool mysql:mysql1');
                                                                    }

                                                                    [$pool, $name] = explode(':', $poolCmd, 2) + ['mysql', 'default'];

                                                                    $factory = match (strtolower($pool)) {
                                                                        'mysql' => \Hyperf\DbConnection\Pool\PoolFactory::class,
                                                                        default => PoolFactory::class,
                                                                    };

                                                                    $pools = di()->get($factory)->getPool($name);

                                                                    $info = [];

                                                                    $info[] = [
                                                                        'pool' => $pool,
                                                                        'poolName' => $name,
                                                                        'currentConnections' => $pools->getCurrentConnections(),
                                                                        'connectionsInChannel' => $pools->getConnectionsInChannel(),
                                                                    ];

                                                                    $this->table($info);
                                                                    break;
                                                                case 'config':
                                                                    // todo 但是不建议获取敏感信息
                                                                    $config = di()->get(ConfigInterface::class);
                                                                    $reflection = new ReflectionClass($config);
                                                                    $property = $reflection->getProperty('configs');
                                                                    $this->out(Json::encode($property->getValue($config)));
                                                                    break;
                                                                case null:
                                                                    break;
                                                                default:
                                                                    if (! ctype_print($command)) {
                                                                        $command = bin2hex($command);
                                                                    }
                                                                    throw new DebuggerException("Unknown command '{$command}'");
                                                            }
                                                        }
                                                    } catch (DebuggerException $exception) {
                                                        $this->out($exception->getMessage());
                                                    } catch (Throwable $throwable) {
                                                        $this->out((string) $throwable);
                                                    }
                                            }
                                        }

                                        // no break
                                    default:
                                        $connection->error(Status::NOT_FOUND);
                                }
                            } catch (ProtocolException $exception) {
                                $connection->error($exception->getCode(), $exception->getMessage(), close: true);
                                break;
                            }
                            if (! $connection->shouldKeepAlive()) {
                                break;
                            }
                        }
                    } catch (Exception) {
                        // you can log error here
                    } finally {
                        $connection->close();
                    }
                });
            } catch (SocketException|CoroutineException|Throwable $exception) {
                if (in_array($exception->getCode(), [Errno::EMFILE, Errno::ENFILE, Errno::ENOMEM], true)) {
                    sleep(1);
                } else {
                    break;
                }
            }
        }
    }

    public function run(string $keyword = ''): static
    {
        if (static::isAlone()) {
            $this->daemon = false;
            $this->logo()->out('[Info]    Program is running...');
            $this->out('[Info]    Press Ctrl+C to stop the server...');
        }
        return $this;
    }

    public function out(string $string = '', bool $newline = true): static
    {
        if ($this->socket === null) {
            return parent::out($string);
        }
        $this->socket->broadcastWebSocketFrame(Psr7::createWebSocketTextFrame(
            payloadData: $string
        ));
        return $this;
    }
}
