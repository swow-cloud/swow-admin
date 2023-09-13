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

use App\Model\System\SystemMenu;

class MenuService
{
    public function add(array $info = []) {}

    public function generateCrudButton(SystemMenu $model): void
    {
        $buttonMenus = [
            ['name' => $model->name . '列表', 'code' => $model->code . ':index'],
            ['name' => $model->name . '保存', 'code' => $model->code . ':add'],
            ['name' => $model->name . '更新', 'code' => $model->code . ':update'],
            ['name' => $model->name . '删除', 'code' => $model->code . ':delete'],
            ['name' => $model->name . '读取', 'code' => $model->code . ':get'],
        ];
        // todo 生成操作按钮
    }
}
