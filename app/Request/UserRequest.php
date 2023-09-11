<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Request;

use CloudAdmin\Validation\Request\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required',
            'password' => 'required|regex:/^[A-Za-z0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'attribute必须填写!',
            'password.required' => ':attribute是必填的!',
            'password.regex' => ':attribute必须是英文字母或数字!',
        ];
    }
}
