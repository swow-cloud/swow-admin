<?php

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Monitor;
use CloudAdmin\Profiler\DataAnalysis\Profile;
use Hyperf\Codec\Json;

class ProfileService
{
    public function flame(int $id):array{
        $data = [
            'wt' => [],
            'mu' => []
        ];
        $model = Monitor::find($id)->first();
        if(!$model){
            throw new BusinessException(ErrorCode::NOT_FOUND);
        }
        $list = Json::decode($model->profile);
        $profileData = \Hyperf\Support\make(Profile::class,[$list]);
        $data['wt'] = $profileData->getFlamegraph('wt',0)['data'];
        $data['mu'] = $profileData->getFlamegraph('mu',0)['data'];
        return $data;
    }
}