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
 * @property string $request_url
 * @property string $app_name
 * @property string $request_body
 * @property int $pmu
 * @property int $wt
 * @property int $cpu
 * @property int $ct
 * @property int $mu
 * @property int $request_time
 * @property int $request_time_micro
 * @property string $profile
 * @property string $type
 * @property string $request_ip
 * @property string $response
 */
class Monitor extends Model
{
    public bool $timestamps = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'monitor';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'request_url', 'app_name', 'request_body', 'pmu', 'wt', 'cpu', 'ct', 'mu', 'request_time', 'request_time_micro', 'profile', 'type', 'request_ip', 'response'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'pmu' => 'integer', 'wt' => 'integer', 'cpu' => 'integer', 'ct' => 'integer', 'mu' => 'integer', 'request_time' => 'integer', 'request_time_micro' => 'integer'];
}
