<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Middleware\Auth;

use App\Exception\AuthException;
use App\Kernel\Http\Response;
use Hyperf\Di\Annotation\Inject;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swow\Http\Status;

class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected Response $response;

    #[Inject]
    protected JWT $jwt;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 判断是否为noCheckRoute
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        if ($this->jwt->matchRoute(null, $method, $path)) {
            return $handler->handle($request);
        }

        $token = $request->getHeaderLine('Authorization') ?? '';

        if ($token === '') {
            return $this->response->handleException(new AuthException(Status::BAD_REQUEST));
        }

        $token = JWTUtil::handleToken($token);

        if ($token !== false && $this->jwt->verifyToken($token)) {
            return $handler->handle($request);
        }

        return $this->response->handleException(new AuthException(Status::UNAUTHORIZED, 'Token authentication does not pass'));
    }
}
