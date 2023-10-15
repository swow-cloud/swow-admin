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

use BeBat\ConsoleColor\Style;
use Hyperf\Codec\Json;
use JetBrains\PhpStorm\ArrayShape;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LogLevel;
use Swow\Buffer;
use Swow\Socket;

use function is_array;
use function sprintf;

class SwowSocketHandler extends AbstractProcessingHandler
{
    protected Socket $output;

    protected Buffer $buffer;

    protected Style $style;

    protected bool $useLocking;

    public function __construct(
        int|Level|string $level = Level::Debug,
        bool $bubble = true,
        bool $useLocking = false,
    ) {
        parent::__construct($level, $bubble);
        $this->output = new Socket(Socket::TYPE_STDOUT);
        $this->buffer = new Buffer(0);
        $this->style = new Style();
        $this->useLocking = $useLocking;
    }

    protected function write(LogRecord $record): void
    {
        $this->buffer->append($this->formatStdoutLogText($record));
        $this->output->send($this->buffer->toString());
        $this->buffer->clear();
    }

    protected function getColorFromLevel(string $level = LogLevel::DEBUG): Style\Color
    {
        return match ($level) {
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL => Style\Color::Red,
            LogLevel::ERROR => Style\Color::Yellow,
            LogLevel::WARNING, LogLevel::INFO, LogLevel::NOTICE => Style\Color::Green,
            default => Style\Color::Default,
        };
    }

    protected function formatStdoutLogText(LogRecord $record): string
    {
        $message = '';

        foreach ($this->toPsrLogRecordColor() as $map => $color) {
            if ($map === 'level_name') {
                continue;
            }

            $isChannel = $map === 'channel';

            if ($isChannel) {
                $color = $this->getColorFromLevel($record->level->toPsrLogLevel());
            }

            $mapVal = $isChannel
                ? sprintf('[%s.%s]', $record->channel, $record->level->getName())
                    . ' '
                : $record->{$map};
            $mapVal = is_array($mapVal) ? Json::encode($mapVal) : $mapVal ?? ' ';
            $message .= $this->style->apply((string) $mapVal . ' ', $color);
        }

        $message .= PHP_EOL;
        return $message;
    }

    #[ArrayShape([
        'message' => 'int',
        'context' => 'int',
        'level' => 'int',
        'level_name' => 'int',
        'channel' => 'int',
        'datetime' => 'int',
        'extra' => 'int',
    ])]
    protected function toPsrLogRecordColor(): array
    {
        return [
            'datetime' => Style\Color::Cyan,
            'channel' => Style\Color::BrightWhite,
            'level_name' => Style\Color::BrightBlue,
            'message' => Style\Color::Green,
            'context' => Style\Color::Magenta,
            'extra' => Style\Color::Yellow,
        ];
    }
}
