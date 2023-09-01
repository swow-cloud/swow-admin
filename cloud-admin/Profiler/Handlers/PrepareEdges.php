<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Profiler\Handlers;

use CloudAdmin\Profiler\EventInterface;

use function array_merge;
use function array_reverse;
use function explode;
use function round;

final class PrepareEdges implements EventInterface
{
    public function handle(array $event): array
    {
        $data = array_reverse($event['profile'] ?? []);

        $event['edges'] = [];

        $id = 1;
        foreach ($data as $name => $values) {
            [$parent, $func] = $this->splitName($name);
            $values = array_merge($values, [
                'p_cpu' => round($values['cpu'] > 0 ? ($values['cpu'] / $event['peaks']['cpu'] * 100) : 0, 2),
                'p_mu' => round($values['mu'] > 0 ? ($values['mu'] / $event['peaks']['mu'] * 100) : 0, 2),
                'p_pmu' => round($values['pmu'] > 0 ? ($values['pmu'] / $event['peaks']['pmu'] * 100) : 0, 2),
                'p_wt' => round($values['wt'] > 0 ? ($values['wt'] / $event['peaks']['wt'] * 100) : 0, 2),
            ]);
            $event['edges']['e' . $id] = [
                'caller' => $parent,
                'callee' => $func,
                'cost' => $values,
            ];

            ++$id;
        }

        return $event;
    }

    /**
     * @return array{0: null|string, 1: string}
     */
    private function splitName(string $name): array
    {
        $a = explode('==>', $name);
        if (isset($a[1])) {
            return $a;
        }

        return [null, $a[0]];
    }
}
