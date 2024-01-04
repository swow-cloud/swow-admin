<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Vo;

use Hyperf\Collection\Collection as HyperfCollection;

final class Collection extends HyperfCollection
{
    public static function tree(array $data, string $idKey, string $parentKey, string $nestKey = 'children'): array
    {
        $trees = $refs = [];

        foreach ($data as &$node) {
            $node[$nestKey] = [];
            $refs[$node[$idKey]] = &$node;

            if ($node[$parentKey]) {
                $refs[$node[$parentKey]][$nestKey][] = &$node;
            } else {
                $trees[] = &$node;
            }
        }
        return $trees;
    }
}
