<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Validation\Request;

use Hyperf\Collection\Arr;
use Hyperf\Validation\Request\FormRequest as HyperfRequest;

use function call_user_func_array;
use function is_array;
use function sprintf;

final class FormRequest extends HyperfRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function getRules(): array
    {
        $scene = $this->getScene();
        $rules = $scene ? call_user_func_array([$this, sprintf('%sRules', $scene)], $this->rules()) : [];
        if ($scene && isset($this->scenes[$scene]) && is_array($this->scenes[$scene])) {
            return Arr::only($rules, $this->scenes[$scene]);
        }
        return $rules;
    }
}
