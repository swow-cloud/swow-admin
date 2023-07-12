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

use CloudAdmin\HttpMessage\SwowStream;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->logger->error(
            sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile())
        );
        $this->logger->error($throwable->getTraceAsString());
        if (\Hyperf\Support\env('APP_DEBUG')) {
            return $response->withHeader('Server', 'Swow')->withStatus(500)->withBody(
                new SwowStream($throwable->getTraceAsString())
            );
        }
        return $response->withHeader('Server', 'Swow')->withStatus(500)->withBody(
            new SwowStream('Internal Server Error.')
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
