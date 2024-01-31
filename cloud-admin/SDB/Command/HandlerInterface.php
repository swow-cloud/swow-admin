<?php
declare(strict_types=1);

namespace CloudAdmin\SDB\Command;

use CloudAdmin\SDB\Parser;

interface HandlerInterface
{
    public function run(Parser $parser): string|null;
}