<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use /*
 * Class ErrorCode
 *
 * This class defines constants for error codes used in the application.
 */
App\Constants\ErrorCode;
use /*
 * Exception class for business-related errors.
 *
 * This exception should be thrown when there is a business-related error in the application.
 * It is meant to be caught and handled appropriately by the application logic.
 *
 * @package App\Exception
 */
App\Exception\BusinessException;
use /*
 * Class Response
 *
 * Represents an HTTP response.
 */
App\Kernel\Http\Response;
use /*
 * Interface StdoutLoggerInterface
 *
 * This interface defines the contract for a logger that logs messages to the standard output.
 */
Hyperf\Contract\StdoutLoggerInterface;
use /*
 * Exception thrown when a circular dependency is detected in the Hyperf dependency injection container.
 *
 * @package Hyperf\Di\Exception
 */
Hyperf\Di\Exception\CircularDependencyException;
use /*
 * Class ExceptionHandler
 *
 * This class handles exceptions in the Hyperf application and provides a customizable
 * way to handle different types of exceptions.
 *
 * @package Hyperf\ExceptionHandler
 */
Hyperf\ExceptionHandler\ExceptionHandler;
use /*
 * Class HttpException
 *
 * The HttpException class is used to represent an HTTP exception.
 * This exception can be thrown when there is an error related to HTTP.
 */
Hyperf\HttpMessage\Exception\HttpException;
use /*
 * Interface ContainerInterface
 *
 * The ContainerInterface defines a minimal interface that a dependency injection container must implement.
 * It provides methods to retrieve services and check if a service is defined within the container.
 */
Psr\Container\ContainerInterface;
use /*
 * PSR-7 Response Interface
 *
 * This interface represents an HTTP response message.
 *
 * @see https://www.php-fig.org/psr/psr-7/#3-psr-http-message-responseinterface
 */
Psr\Http\Message\ResponseInterface;
use /*
 * Interface LoggerInterface
 *
 * The LoggerInterface defines the standard methods that a logger implementation should implement.
 *
 * @package Psr\Log
 */
Psr\Log\LoggerInterface;
use /*
 * Represents the base class for all errors and exceptions.
 *
 * @since PHP 5, PHP 7
 */
Throwable;

use function /*
 * This class provides utility methods for formatting and handling throwables (exceptions and errors).
 */
CloudAdmin\Utils\formatThrowable;

/**
 * Class BusinessExceptionHandler.
 *
 * This class handles business exceptions and transforms them into appropriate HTTP responses.
 * It extends the ExceptionHandler class provided by the Hyperf framework.
 */
final class BusinessExceptionHandler extends ExceptionHandler
{
    protected Response $response;

    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(Response::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Handle a thrown exception and generate an appropriate response.
     *
     * @param Throwable $throwable the thrown exception
     * @param ResponseInterface $response the response object to be returned
     *
     * @return ResponseInterface the generated response
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        switch (true) {
            case $throwable instanceof HttpException:
                $this->stopPropagation();
                return $this->response->handleException($throwable);
            case $throwable instanceof BusinessException:
                $this->stopPropagation();
                $this->logger->warning(formatThrowable($throwable));
                if ($throwable->errMsgIsFromErrorCode) {
                    return $this->response->fail($throwable->getMessage());
                }
                return $this->response->fail($throwable->getErrorCode());
            case $throwable instanceof CircularDependencyException:
                $this->logger->error($throwable->getMessage());
                return $this->response->fail(ErrorCode::SERVER_ERROR->value);
        }

        $this->logger->error(formatThrowable($throwable));

        return $this->response->fail(ErrorCode::SERVER_ERROR);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
