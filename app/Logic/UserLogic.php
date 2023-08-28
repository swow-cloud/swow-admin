<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Logic;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Phper666\JWTAuth\JWT;
use Psr\SimpleCache\InvalidArgumentException;

class UserLogic
{
    #[Inject]
    protected UserService $userService;

    #[Inject]
    protected JWT $JWT;

    /**
     * @throws InvalidArgumentException
     */
    public function login(string $username, string $password): array
    {
        $user = $this->userService->login($username, $password);

        $token = $this->JWT->getToken('cloud-admin', [
            'uid' => $user->id,
            'username' => $user->username,
        ]);

        return ['token' => $token->toString(), 'expire_in' => $this->JWT->getTTL($token->toString())];
    }
}
