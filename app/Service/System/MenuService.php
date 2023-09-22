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

use App\Constants\Status;
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

    //todo 待处理分页问题
    public function list(array|string $selects, array $params, array $order = []): array
    {
        $model = make(SystemMenu::class);
        return $model->getPageList($selects, $params, $order);
    }
}
