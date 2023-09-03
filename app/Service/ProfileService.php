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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Monitor;
use CloudAdmin\Profiler\DataAnalysis\Profile;
use Hyperf\Codec\Json;

class ProfileService
{
    public function flame(int $id): array
    {
        $data = [
            'wt' => [],
            'mu' => [],
        ];
        $model = Monitor::find($id)->first();
        if (! $model) {
            throw new BusinessException(ErrorCode::NOT_FOUND);
        }
        $list = Json::decode($model->profile);
        $profileData = \Hyperf\Support\make(Profile::class, [$list]);
        $data['wt'] = $profileData->getFlamegraph('wt', 0)['data'];
        $data['mu'] = $profileData->getFlamegraph('mu', 0)['data'];
        return $data;
    }
}
