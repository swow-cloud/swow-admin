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

use App\Event\UserLoggedInEvent;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Phper666\JWTAuth\JWT;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class UserLogic
{
    #[Inject]
    private UserService $userService;

    #[Inject]
    private EventDispatcherInterface $eventDispatcher;

    #[Inject]
    private JWT $JWT;

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

        $this->eventDispatcher->dispatch(new UserLoggedInEvent($user->id, $user->username));

        return ['access_token' => $token->toString(), 'expire_in' => $this->JWT->getTTL($token->toString())];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function logout(string $token): bool
    {
        return $this->JWT->logout($token);
    }
}
