<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Model;

/**
 * @property int $user_id 用户主键
 * @property int $role_id 角色主键
 */
class SystemUserRole extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'system_user_role';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['user_id', 'role_id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['user_id' => 'integer', 'role_id' => 'integer'];
}
