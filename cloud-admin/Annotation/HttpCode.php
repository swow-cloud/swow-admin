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
use Hyperf\Di\Annotation\AbstractAnnotation;
use Swow\Http\Status;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class HttpCode extends AbstractAnnotation
{
    /**
     * @param int $code
     * @phpstan-param int $code
     */
    public function __construct(public readonly int $code = Status::OK) {}
}
