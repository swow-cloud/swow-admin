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

final class PreparePeaks implements EventInterface
{
    public function handle(array $event): array
    {
        $event['peaks'] = $event['profile']['main()'] ?? [
            'wt' => 0,
            'ct' => 0,
            'mu' => 0,
            'pmu' => 0,
        ];
        return $event;
    }
}
