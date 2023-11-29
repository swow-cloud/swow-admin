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

use App\Kernel\Http\Response;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class FormValidateExceptionHandler extends ExceptionHandler
{
    #[Inject]
    public Response $response;

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        /** @var ValidationException $throwable */
        if ($this->isValid($throwable)) {
            $this->stopPropagation();
            return $this->response->fail($throwable->validator->errors()->first());
        }
        return $response;
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
