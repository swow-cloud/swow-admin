<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Annotation;

use Attribute;
use Swow\Http\Status;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class HttpCode
{
    public readonly int $code;

    public function __construct(int $code = Status::OK)
    {
        $this->code = $code;
    }
}
