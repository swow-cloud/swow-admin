<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Request\System;

use CloudAdmin\Validation\Request\FormRequest;

final class MenuRequest extends FormRequest
{
    public function addRules(): array
    {
        return [
            'name' => 'required|max:30',
            'code' => 'required|min:3|max:50',
        ];
    }

    public function updateRules(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|max:30',
            'code' => 'required|min:3|max:50',
        ];
    }

    public function changeStatusRules(): array
    {
        return [
            'id' => 'required',
            'status' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => '菜单ID',
            'name' => '菜单名称',
            'code' => '菜单标识',
            'status' => '菜单状态',
        ];
    }
}
