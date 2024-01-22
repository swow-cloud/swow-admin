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
use BeBat\ConsoleColor\Style\Color;
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

final class SwowSocketHandler extends AbstractProcessingHandler
{
    /**
     * @phpstan-var  Socket Socket
     */
    protected Socket $output;

    /**
     * @phpstan-var  Buffer Buffer
     */
    protected Buffer $buffer;

    /**
     * @phpstan-var Style $style
     */
    protected Style $style;

    /**
     * @phpstan-param int|Level|string $level
     * @phpstan-param bool $bubble
     * @phpstan-param bool $useLocking
     */
    public function __construct(
        int|Level|string $level = Level::Debug,
        bool $bubble = true,
        protected bool $useLocking = false,
    ) {
        parent::__construct($level, $bubble);
        $this->output = new Socket(Socket::TYPE_STDOUT);
        $this->buffer = new Buffer(0);
        $this->style = new Style();
    }

    /**
     * @phpstan-param LogRecord $record
     */
    protected function write(LogRecord $record): void
    {
        $this->buffer->append($this->formatStdoutLogText($record));
        $this->output->send($this->buffer->toString());
        $this->buffer->clear();
    }

    /**
     * @phpstan-param LogRecord $record
     * @phpstan-return Color
     */
    protected function getColorFromLevel(string $level = LogLevel::DEBUG): Color
    {
        return match ($level) {
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL => Color::Red,
            LogLevel::ERROR => Color::Yellow,
            LogLevel::WARNING, LogLevel::INFO, LogLevel::NOTICE => Color::Green,
            default => Color::Default,
        };
    }

    /**
     * @phpstan-param LogRecord $record
     * @phpstan-return string
     */
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
    /**
     * @phpstan-return array<string, int>
     */
    protected function toPsrLogRecordColor(): array
    {
        return [
            'datetime' => Color::Cyan,
            'channel' => Color::BrightWhite,
            'level_name' => Color::BrightBlue,
            'message' => Color::Green,
            'context' => Color::Magenta,
            'extra' => Color::Yellow,
        ];
    }
}
