<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Logic;

use App\Service\SmsService;
use Hyperf\Di\Annotation\Inject;
use JetBrains\PhpStorm\ArrayShape;

class SmsLogic
{
    #[Inject]
    public SmsService $smsService;

    #[ArrayShape(['id' => 'string', 'phone' => 'string', 'verify_code' => 'string', 'send_time' => 'string'])]
    public function send(string $phone): array
    {
        return $this->smsService->send($phone);
    }
}
