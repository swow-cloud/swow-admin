<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\Log\LoggerFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Strategy\CoroutineStrategy;
use Hyperf\Crontab\Strategy\StrategyInterface;

return [
    StdoutLoggerInterface::class => LoggerFactory::class,
    StrategyInterface::class => CoroutineStrategy::class,
];
