<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Kernel\Http\Response;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Exception\CircularDependencyException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function CloudAdmin\Utils\formatThrowable;

class BusinessExceptionHandler extends ExceptionHandler
{
    protected Response $response;

    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(Response::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        switch (true) {
            case $throwable instanceof HttpException:
                return $this->response->handleException($throwable);
            case $throwable instanceof BusinessException:
                $this->logger->warning(formatThrowable($throwable));
                return $this->response->fail($throwable->getCode());
            case $throwable instanceof CircularDependencyException:
                $this->logger->error($throwable->getMessage());
                return $this->response->fail(ErrorCode::SERVER_ERROR->value);
        }

        $this->logger->error(formatThrowable($throwable));

        return $this->response->fail(ErrorCode::SERVER_ERROR->value);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
