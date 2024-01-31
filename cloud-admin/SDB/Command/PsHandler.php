<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\SDB\Command;

use CloudAdmin\SDB\Parser;

use function var_dump;

final class PsHandler implements HandlerInterface
{
    public function run(Parser $parser): null|string
    {
        var_dump($parser->toArray());
    }
}
