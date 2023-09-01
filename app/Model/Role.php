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
 * @property string $name
 * @property string $description
 */
class Role extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'roles';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'description'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer'];
}
