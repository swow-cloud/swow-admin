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

use Hyperf\Validation\Request\FormRequest;

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
            'username.required' => '用户名必须填写!',
            'password.required' => '密码必须填写!',
            'password.regex' => '密码必须带有数字和字符串!',
        ];
    }
}
