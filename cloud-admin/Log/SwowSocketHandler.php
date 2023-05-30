<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Log;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LogLevel;
use Swow\Buffer;
use Swow\Socket;

class SwowSocketHandler extends AbstractProcessingHandler
{
    protected Socket $output;

    protected Buffer $buffer;

    protected Color $color;

    protected bool $useLocking;

    public function __construct(int|string|Level $level = Level::Debug, bool $bubble = true, bool $useLocking = false)
    {
        parent::__construct($level, $bubble);

        $this->output = new Socket(Socket::TYPE_STDOUT);
        $this->buffer = new Buffer(0);
        $this->color = new Color();

        $this->useLocking = $useLocking;
    }

    protected function write(LogRecord $record): void
    {
        $message = $this->color->out(
            (string) $record->formatted,
            $this->getColorFromLevel($record->level->toPsrLogLevel())
        );
        $this->buffer->append($message);

        $this->output->send($this->buffer->toString());
    }

    protected function getColorFromLevel(string $level = LogLevel::DEBUG): string
    {
        return match ($level) {
            LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL => 'red',
            LogLevel::ERROR => 'yellow',
            LogLevel::WARNING, LogLevel::INFO, LogLevel::NOTICE => 'green',
            default => 'blue',
        };
    }
}
