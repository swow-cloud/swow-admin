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
            $page[$this::PAGE_NAME] ?? $this::PAGE_NAME,
            $params[$this::PAGE_NAME] ?? 1
        );
        return $this->setPaginate($paginate, $params);
    }

    public function setPaginate(LengthAwarePaginatorInterface $paginator, array $params = []): array
    {
        return [
            'items' => $paginator->items(),
            'pageInfo' => [
                'total' => $paginator->total(),
                'currentPage' => $paginator->currentPage(),
                'totalPage' => $paginator->lastPage(),
            ],
        ];
    }

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
