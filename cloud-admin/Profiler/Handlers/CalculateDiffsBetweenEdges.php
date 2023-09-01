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

final class CalculateDiffsBetweenEdges implements EventInterface
{
    public function handle(array $event): array
    {
        $data = array_reverse($event['profile'] ?? []);
        $parents = [];
        foreach ($data as $name => $values) {
            [$parent, $func] = $this->splitName($name);

            if ($parent) {
                $parentValues = $parents[$parent] ?? ['cpu' => 0, 'wt' => 0, 'mu' => 0, 'pmu' => 0];
                $event['profile'][$name] = array_merge([
                    'd_cpu' => $parentValues['cpu'] - $values['cpu'],
                    'd_wt' => $parentValues['wt'] - $values['wt'],
                    'd_mu' => $parentValues['mu'] - $values['mu'],
                    'd_pmu' => $parentValues['pmu'] - $values['pmu'],
                ], $values);
            }

            $parents[$func] = $values;
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
