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
use App\Model\Sys\SystemUser;
use Carbon\Carbon;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ServerRequestInterface;

use function CloudAdmin\Utils\ip;
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

            /** @var SystemUser $user */
            $user = SystemUser::query()->find($event->userId);
            $request = Context::get(ServerRequestInterface::class);
            $user->login_ip = ip($request);
            $user->save();
        }
    }
}
