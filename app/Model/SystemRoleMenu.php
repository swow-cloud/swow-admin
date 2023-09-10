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
 * @property int $role_id 角色主键
 * @property int $menu_id 菜单主键
 */
class SystemRoleMenu extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'system_role_menu';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['role_id', 'menu_id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['role_id' => 'integer', 'menu_id' => 'integer'];
}
