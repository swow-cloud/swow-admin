<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Service;

use App\Component\Password;
use App\Constants\ErrorCode;
use App\Constants\Status;
use App\Exception\BusinessException;
use App\Model\System\SystemUser;

final class UserService
{
    /**
     * @throws BusinessException
     */
    public function login(string $username, string $password): SystemUser
    {
        /** @var SystemUser $userModel */
        $userModel = SystemUser::query()->where(['username' => $username, 'status' => Status::ACTIVE])->first();
        if (! $userModel) {
            throw new BusinessException(ErrorCode::USER_LOGIN_PASSWORD_ERR);
        }

        if (! Password::verify($password, $userModel->password)) {
            throw new BusinessException(ErrorCode::USER_LOGIN_PASSWORD_ERR);
        }

        // 检测是否有更新的可用散列算法
        if (Password::needsRehash($userModel->password)) {
            $userModel->password = Password::hash($password);
        }

        if ($userModel->isDirty('password')) {
            $userModel->save();
        }

        return $userModel;
    }

    /**
     * 获取用户信息.
     */
    public static function get(int $uid): SystemUser
    {
        return SystemUser::find($uid);
    }
}
