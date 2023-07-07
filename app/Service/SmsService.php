<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Service;

use App\Component\Code;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\SmsLog;
use Carbon\Carbon;

class SmsService
{
    //todo
    public function send(string $phone): bool
    {
        $code = Code::generateSmsVerifyCode();

        $smsLog = new SmsLog();
        $smsLog->phone = $phone;
        $smsLog->verify_code = $code;
        $smsLog->send_time = Carbon::now()->toDateTimeString();

        if ($smsLog->save()) {
            return true;
        }
        throw new BusinessException(ErrorCode::SMS_FAILED_TO_SYNC_SMS_LOGS);
    }
}
