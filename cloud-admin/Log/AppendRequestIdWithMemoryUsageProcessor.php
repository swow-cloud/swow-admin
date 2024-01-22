<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Log;

use CloudAdmin\Utils\Os;
use Hyperf\Context\Context;
use Hyperf\Engine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

use function memory_get_usage;
use function round;
use function uniqid;

final class AppendRequestIdWithMemoryUsageProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'log.request.id';

    /**
     * @phpstan-var  bool Whether to format memory usage
     */
    public bool $useFormatting = true;

    /**
     * @phpstan-param  LogRecord $record
     * @phpstan-return  array|LogRecord
     */
    public function __invoke(LogRecord $record): array|LogRecord
    {
        $record['extra']['request_id'] = Context::getOrSet(
            self::REQUEST_ID,
            uniqid('swow-admin', true),
        );
        $record['extra']['coroutine_id'] = Coroutine::id();
        $record['extra']['process_id'] = Os::getProcessId();
        $usage = memory_get_usage(true);

        if ($this->useFormatting) {
            $usage = $this->formatBytes($usage);
        }

        $record['extra']['memory_usage'] = $usage;
        return $record;
    }

    /**
     * @phpstan-param  int $bytes
     * @phpstan-return  int|string
     */
    private function formatBytes(int $bytes): int|string
    {
        if (! $this->useFormatting) {
            return $bytes;
        }

        if ($bytes > 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . ' MB';
        }

        if ($bytes > 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
