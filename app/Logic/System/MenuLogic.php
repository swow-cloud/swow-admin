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

use function array_merge;
use function array_values;

class MenuLogic
{
    #[Inject]
    public MenuService $menuService;

    public function add(array $data): int
    {
        $menuData = $this->makeMenuData($data);
        $meuModel = $this->menuService->add($menuData);
        if ($data['type'] === SystemMenu::MENU && (int) $data['crud'] === 1) {
            $this->generateCrudButton($meuModel);
        }
        return $meuModel->id;
    }

    public function generateCrudButton(SystemMenu $model): void
    {
        $buttonMenus = [
            ['name' => $model->name . '列表', 'code' => $model->code . ':index'],
            ['name' => $model->name . '保存', 'code' => $model->code . ':add'],
            ['name' => $model->name . '更新', 'code' => $model->code . ':update'],
            ['name' => $model->name . '删除', 'code' => $model->code . ':delete'],
            ['name' => $model->name . '读取', 'code' => $model->code . ':get'],
        ];
        foreach ($buttonMenus as $buttonMenu) {
            $this->add(array_merge(
                ['parent_id' => $model->id, 'type' => SystemMenu::BUTTON],
                $buttonMenu,
            ));
        }
    }

    public function treeMenu(): array
    {
        return array_values($this->menuService->tree());
    }

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
