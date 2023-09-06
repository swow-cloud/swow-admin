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

use function date;

class ProfileService
{
    /**
     * @return array{wt: array|mixed, mu: array|mixed}
     */
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
        $data['wt'] = $profileData->getFlameGraph('wt', 0)['data'];
        $data['mu'] = $profileData->getFlameGraph('mu', 0)['data'];
        return $data;
    }

    /**
     * @return array{appName: array, funcList: array|mixed, flameGraph: array, sql: array|string, base: array|string[]}
     */
    public function info(int $id): array
    {
        $data = [
            'appName' => [],
            'funcList' => [],
            'flameGraph' => [],
            'sql' => [],
            'base' => [
                'url' => '',
                'type' => '',
                'request_time' => '',
                'wt' => '',
                'mu' => '',
                'ip' => '',
            ],
        ];
        $model = Monitor::find($id)->first();
        if (! $model) {
            throw new BusinessException(ErrorCode::NOT_FOUND);
        }
        $profile = Json::decode($model->profile, true);
        $data['sql'] = '';
        $ProfileData = \Hyperf\Support\make(Profile::class, [$profile]);
        $data['funcList'] = $ProfileData->getProfileBySort();
        $data['base'] = [
            'url' => $model->url,
            'type' => $model->type,
            'request_time' => date('Y-m-d H:i:s', $model->request_time),
            'wt' => $model->wt,
            'mu' => $model->mu,
            'ip' => $model->request_ip,
        ];
        return $data;
    }
}
