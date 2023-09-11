<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Trait;

use CloudAdmin\Model\Model;
use Hyperf\Database\Query\Builder;

// todo: 处理分页的特征类
trait PaginateTrait
{
    public Model $model;

    public function getList(array $params) {}

    public function getPageList(array $params) {}

    public function setPaginate(): array {}

    public function order(Builder $builder, array &$params = []): Builder {}
}
