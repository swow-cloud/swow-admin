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

use CloudAdmin\Model\Model;

/**
 * @property int $id
 * @property string $phone
 * @property string $verify_code
 * @property string $send_time
 * @property string $create_time
 * @property string $update_time
 */
final class SmsLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'sms_log';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'phone', 'verify_code', 'send_time', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer'];
}
