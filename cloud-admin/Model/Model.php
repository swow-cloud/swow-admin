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
use Hyperf\Database\Model\Model as HyperfModel;
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

    /**
     * @phpstan-param  array $condition
     * @phpstan-param  array $field
     * @phpstan-param  bool $forUpdate
     * @phpstan-return  Builder|HyperfModel|null
     */
    public static function findOne(array $condition, array $field = ['*'], bool $forUpdate = false): null|Builder|HyperfModel
    {
        $query = self::buildByCondition($condition);
        if ($forUpdate) {
            $query->lockForUpdate();
        }
        return $query->first($field);
    }

    /**
     * @phpstan-param  int $id
     * @phpstan-param  array $field
     * @phpstan-return  Builder|HyperfModel|null
     */
    public static function one(int $id, array $field = ['*']): null|Builder|HyperfModel
    {
        return self::findOne(['id' => $id], $field);
    }

    /**
     * @phpstan-param  array $condition
     * @phpstan-return  Builder
     */
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

    /**
     * @phpstan-param  array $condition
     * @phpstan-param  array $data
     * @phpstan-return  int
     */
    public static function updateCondition(array $condition, array $data): int
    {
        $query = self::buildByCondition($condition);
        return $query->update($data);
    }

    /**
     * @phpstan-param  array $condition
     * @phpstan-return  int
     */
    public static function countCondition(array $condition): int
    {
        $query = self::buildByCondition($condition);
        return $query->count();
    }

    /**
     * @phpstan-param  Builder $model
     * @phpstan-param  string $field
     * @phpstan-param  array $createTime
     * @phpstan-return  void
     */
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
