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

use Aimeos\Map;

class Collection extends \Hyperf\Collection\Collection
{
    public static function tree(array $data, string $idKey, string $parentKey, string $nestKey = 'children'): array
    {
        return Map::from($data)->tree($idKey, $parentKey, $nestKey)->values()->toArray();
    }
}
