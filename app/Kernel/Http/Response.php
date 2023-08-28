<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Kernel\Http;

use App\Constants\ErrorCodeInterface;
use CloudAdmin\HttpMessage\SwowStream;
use Exception;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response
{
    public const OK = 0;

    public const ERROR = -1;

    protected ResponseInterface $response;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(ResponseInterface::class);
    }

    public function success(mixed $data = [], string $message = 'ok'): PsrResponseInterface
    {
        return $this->response->json([
            'code' => self::OK,
            'data' => $data,
            'message' => $message,
        ]);
    }

    public function fail(ErrorCodeInterface|string|Exception $error): PsrResponseInterface
    {
        if ($error instanceof ErrorCodeInterface) {
            $error = $error->getMessage();
        }

        return $this->response->json([
            'code' => self::ERROR,
            'message' => (string) $error,
        ]);
    }

    public function redirect($url, int $status = 302): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Location', (string) $url)
            ->withStatus($status);
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Server', 'SwowAdmin')
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwowStream($throwable->getMessage()));
    }

    public function response(): PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }
}
