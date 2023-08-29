<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Event;

class UserLoggedInEvent
{
    public int $userId;

    public string $username;

    public function __construct(int $userId, string $username)
    {
        $this->userId = $userId;
        $this->username = $username;
    }
}
