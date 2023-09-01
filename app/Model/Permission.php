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
 * @property int $id
 * @property int $parent_id
 * @property int $is_display
 * @property string $path
 * @property string $method
 * @property string $display_name
 * @property string $url
 * @property string $validate
 * @property string $description
 * @property int $sort
 */
class Permission extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'parent_id', 'is_display', 'path', 'method', 'display_name', 'url', 'validate', 'description', 'sort'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'parent_id' => 'integer', 'is_display' => 'integer', 'sort' => 'integer'];
}
