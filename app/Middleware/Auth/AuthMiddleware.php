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

use App\Constants\ErrorCode;
use App\Exception\AuthException;
use App\Kernel\Http\Response;
use App\Service\UserService;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swow\Http\Status;

final class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    private readonly Response $response;

    #[Inject]
    private readonly JWT $jwt;

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
            // token为空返回 http_code Status::BAD_REQUEST
            return $this->response->handleException(new AuthException(Status::BAD_REQUEST, \Hyperf\HttpMessage\Base\Response::getReasonPhraseByCode(Status::BAD_REQUEST)));
        }

        $token = JWTUtil::handleToken($token);

        try {
            if ($token !== false && $this->jwt->verifyToken($token)) {
                $this->setUserContextWithToken($token);
                return $handler->handle($request);
            }
        } catch (JWTException|TokenValidException) {
            return $this->response->fail(ErrorCode::UNAUTHORIZED);
        }
        return $this->response->fail(ErrorCode::UNAUTHORIZED);
    }

    private function setUserContextWithToken(string $token): void
    {
        if ($claims = $this->jwt->getClaimsByToken($token)) {
            Context::set('user', UserService::get($claims['uid']));
        }
    }
}
