<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Logic\System;

use App\Model\System\SystemMenu;
use App\Service\System\MenuService;
use Hyperf\Di\Annotation\Inject;

class MenuLogic
{
    #[Inject]
    public MenuService $menuService;

    public function add(array $data) {}

    protected function makeMenuData(array $data): array
    {
        if ((int) $data['parent_id'] === 0) {
            $data['level'] = '0';
            $data['parent_id'] = 0;
            $data['type'] = $data['type'] === SystemMenu::BUTTON ? SystemMenu::MENU : $data['type'];
        } else {
            $parentMenu = SystemMenu::one((int) $data['parent_id']);
            $data['level'] = $parentMenu['level'] . ',' . $parentMenu['id'];
        }
        return $data;
    }
}
