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

use App\Constants\ErrorCode;
use App\Constants\Status;
use App\Exception\BusinessException;
use App\Model\System\SystemMenu;
use App\Service\System\MenuService;
use CloudAdmin\Model\Model;
use CloudAdmin\Vo\Collection;
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

    public function update(array $data): int
    {
        $id = $data['id'];
        unset($data['id']);
        $menuData = $this->makeMenuData($data);
        if ($this->menuService->update($id, $menuData)) {
            return $id;
        }
        throw new BusinessException(ErrorCode::SERVER_ERROR, '修改失败,请稍候再试!');
    }

    public function list(array $params): array
    {
        $params = $this->filterParams($params);
        $selects = ['id', 'parent_id', 'name', 'code', 'icon', 'route', 'type', 'component', 'is_display', 'redirect', 'sort', 'status', 'remark'];
        $page = [
            Model::PAGE_NAME => (int) $params[Model::PAGE_NAME],
            Model::PAGE_SIZE_NAME => (int) $params[Model::PAGE_SIZE_NAME],
        ];
        unset($params[Model::PAGE_NAME], $params[Model::PAGE_SIZE_NAME]);
        // 1.先查询父节点为0的菜单，根据父节点为0的菜单进行分页
        $menus = $this->menuService->list($selects, array_merge(['parent_id' => 0], $params), ['id' => 'asc'], $page);
        // 2.查询子节点
        /** @var SystemMenu $item */
        $data = $this->fetchChildMenus($selects, $menus);
        foreach ($data as &$val) {
            $val = $this->formatMeta($val);
        }
        unset($val);
        $menus['list'] = Collection::tree($data, 'id', 'parent_id');
        return $menus;
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

    private function fetchChildMenus(array $selects, array $menus): array
    {
        $data = [];
        foreach ($menus['list'] as $item) {
            $data[] = $item->toArray();
            $data = array_merge($data, $this->menuService->getChildMenuWithLevel($selects, (string) $item['id']));
        }
        return $data;
    }

    private function formatMeta(array $val): array
    {
        return [
            'meta' => [
                'icon' => $val['icon'],
                'title' => $val['name'],
                'isLink' => $val['type'] === SystemMenu::LINK,
                'isHide' => $val['is_display'] === Status::ACTIVE,
                'isAffix' => $val['name'] === 'home',
                'isKeepAlive' => true,
            ],
            'path' => $val['route'],
            'code' => $val['code'],
            'component' => $val['component'],
            'id' => $val['id'],
            'parent_id' => $val['parent_id'],
            'redirect' => $val['redirect'],
            'sort' => $val['sort'],
            'status' => $val['status'],
            'remark' => $val['remark'],
            'icon' => $val['icon'],
            'name' => $val['name'],
            'is_display' => $val['is_display'],
            'type' => $val['type'],
        ];
    }

    private function filterParams(array $params): array
    {
        if (! empty($params['path'])) {
            $params['route'] = $params['path'];
        }
        if (! empty($params['title'])) {
            $params['name'] = $params['title'];
        }
        unset($params['path'], $params['title']);
        return $params;
    }
}
