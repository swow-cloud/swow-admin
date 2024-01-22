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

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;

use function is_array;

trait PaginateTrait
{
    /**
     * @phpstan-param  array|string $select
     * @phpstan-param  array $params
     * @phpstan-param  array $order
     * @phpstan-return  array
     */
    public function getList(array|string $select, array $params, array $order = []): array
    {
        $query = $this->buildByCondition($params);
        if ($select) {
            $query->select($select);
        }
        if ($order) {
            $query = $this->order($query, $order);
        }

        return $query->get()->toArray();
    }

    /**
     * @phpstan-param  array|string $select
     * @phpstan-param  array $params
     * @phpstan-param  array $order
     * @phpstan-param  array $page
     * @return array{items: array, pageInfo: array}
     */
    public function getPageList(array|string $select, array $params, array $order = [], array $page = []): array
    {
        $query = $this->buildByCondition($params);
        if ($select) {
            $query->select($select);
        }
        if ($order) {
            $query = $this->order($query, $order);
        }
        $paginate = $query->paginate(
            $page[$this::PAGE_SIZE_NAME] ?? $this::PAGE_SIZE,
            ['*'],
            $this::PAGE_NAME,
            $page[$this::PAGE_NAME] ?? 1
        );
        return $this->setPaginate($paginate, $params);
    }

    /**
     * @phpstan-param  LengthAwarePaginatorInterface $paginator
     * @phpstan-param  array $params
     * @return array{list: array, total: int, currentPage: int, totalPage: int}
     */
    public function setPaginate(LengthAwarePaginatorInterface $paginator, array $params = []): array
    {
        return [
            'list' => $paginator->items(),
            'total' => $paginator->total(),
            'pageNum' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }

    /**
     * @phpstan-param  Builder $builder
     * @phpstan-param  array $params
     * @return Builder
     */
    public function order(Builder $builder, array $params = []): Builder
    {
        if ($params) {
            if (is_array($params)) {
                foreach ($params as $key => $sort) {
                    $builder->orderBy($key, $sort ?? 'asc');
                }
            }
        }
        return $builder;
    }
}
