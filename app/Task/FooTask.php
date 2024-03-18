<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

use function date;
use function time;
use function var_dump;

#[Crontab(rule: '* * * * *', name: 'Foo', callback: 'execute', memo: '这是一个示例的定时任务')]
final class FooTask
{
    #[Inject]
    private readonly StdoutLoggerInterface $logger;

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }

    #[Crontab(rule: '* * * * *', memo: 'foo')]
    public function foo()
    {
        var_dump('foo');
    }
}
