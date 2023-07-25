<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\SDB;

use Swow\Debug\Debugger\Debugger;
use Swow\Socket;

# agent解析 https://github.com/buggregator/server/tree/master/app/modules/Profiler 参考资料
# 前端 https://github.com/buggregator/frontend/tree/6b74cce8bf1a5cdebe5338913612a014724f2a29/components/ProfilerPage 参考资料
class WebSocketDebugger extends Debugger
{
    protected $fomart;

    protected Socket $socket;

    final public function __construct()
    {
        parent::__construct();
    }

    final public static function runOnWebSocket(string $keyword = 'sdb'): static
    {
        return static::getInstance()->run($keyword);
    }
}
