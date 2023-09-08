<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Listener;

use App\Event\UserLoggedInEvent;
use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;

use function sprintf;

class UserLoggedInListener implements ListenerInterface
{
    #[Inject]
    protected LoggerFactory $loggerFactory;

    public function __construct() {}

    public function listen(): array
    {
        // return the events that you want to listen
        return [
            UserLoggedInEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof UserLoggedInEvent) {
            $logger = $this->loggerFactory->get();

            $logger->info(sprintf('用户:[%s]-[%s] 在[%s] 登录了系统.', $event->userId, $event->username, Carbon::now()->toDateTimeString()));
        }
    }
}
