<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Model;

use CloudAdmin\Trait\PaginateTrait;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model as BaseModel;

use function is_array;
use function is_int;

class Model extends BaseModel
{
    use PaginateTrait;

    final public const PAGE_SIZE = 10;

    final public const PAGE_NAME = 'pageNum';

    final public const PAGE_SIZE_NAME = 'pageSize';

    protected ?string $dateFormat = 'Y-m-d H:i:s';

    public static function findOne(array $condition, array $field = ['*'], bool $forUpdate = false): \Hyperf\Database\Model\Model
    {
        $query = self::buildByCondition($condition);
        if ($forUpdate) {
            $query->lockForUpdate();
        }
        return $query->first($field);
    }

    public static function one(int $id, array $field = ['*']): \Hyperf\Database\Model\Model
    {
        return self::findOne(['id' => $id], $field);
    }

    public static function buildByCondition(array $condition): Builder
    {
        $query = self::query();
        foreach ($condition as $key => $value) {
            if (is_int($key)) {
                $query->where($condition);
                break;
            }
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }
        return $query;
    }

    public static function updateCondition(array $condition, array $data): int
    {
        $query = self::buildByCondition($condition);
        return $query->update($data);
    }

    public static function countCondition(array $condition): int
    {
        $query = self::buildByCondition($condition);
        return $query->count();
    }

    public static function betweenTime(Builder $model, string $field, array $createTime): void
    {
        $model->where(function (Builder $builder) use ($field, $createTime) {
            if ($createTime['startDate'] > 0) {
                $builder->where($field, '>=', $createTime['startDate']);
            }
            if ($createTime['endDate'] > 0) {
                $builder->where($field, '<', $createTime['endDate']);
            }
        });
    }
}
