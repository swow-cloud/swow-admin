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

use function explode;
use function intval;
use function preg_match;

final class CatDebugLog
{
    public const LOG_LINE_PATTERN = '/^\[\s*(.*?)\s*]\s*(.*?)\((.*?)\): <(.*?)> (.*?) in (.*?):(\d+)$/';

    /**
     * @phpstan-return  array<string, mixed>|null
     */
    public function parseSingleLogLine(string $logLine): ?array
    {
        if (preg_match(self::LOG_LINE_PATTERN, $logLine, $matches)) {
            return [
                'module' => $matches[1],
                'level' => $matches[2],
                'version' => $matches[3],
                'tag' => $matches[4],
                'details' => $matches[5],
                'file_path' => $matches[6],
                'line_number' => intval($matches[7]),
            ];
        }
        return null;
    }

    /**
     * Parses a debug log and returns an array of parsed log entries.
     *
     * @phpstan-return array<int, array<string, mixed>>
     */
    public function parseDebugLog(string $logContent): array
    {
        $logLines = explode("\n", $logContent);
        $parsedLogs = [];
        foreach ($logLines as $logLine) {
            $parsedLog = $this->parseSingleLogLine($logLine);
            if ($parsedLog !== null) {
                $parsedLogs[] = $parsedLog;
            }
        }
        return $parsedLogs;
    }
}
