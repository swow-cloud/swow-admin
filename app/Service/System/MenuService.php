<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Service\System;

use App\Constants\ErrorCode;
use App\Constants\Status;
use App\Exception\BusinessException;
use App\Model\System\SystemMenu;
use CloudAdmin\Vo\Collection;

use function make;

class MenuService
{
    public function add(array $data = []): SystemMenu
    {
        return SystemMenu::create($data);
    }

    public function tree(): array
    {
        return Collection::tree(SystemMenu::buildByCondition(['status' => Status::ACTIVE, 'type' => SystemMenu::MENU])->get(['id', 'parent_id', 'id AS value', 'name AS label'])->toArray(), 'id', 'parent_id');
    }

    public function list(array|string $selects, array $params, array $order = [], array $page = []): array
    {
        $model = make(SystemMenu::class);
        return $model->getPageList($selects, $params, $order, $page);
    }

    public function getChildMenuWithLevel(array|string $selects, string $level = '0'): array
    {
        $model = make(SystemMenu::class);
        return $model->buildByCondition(['status' => Status::ACTIVE, 'type' => SystemMenu::MENU])->where('level', 'like', '%' . $level . '%')->get($selects)->toArray();
    }

    public function update(array $data): bool
    {
        $model = SystemMenu::find($data['id']);
        if (! $model) {
            throw new BusinessException(ErrorCode::NOT_FOUND);
        }
        return $model->update($data);
    }
}
