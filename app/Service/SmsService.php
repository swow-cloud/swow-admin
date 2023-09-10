<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
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
use App\Model\SystemUser;
use Carbon\Carbon;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

class SmsService
{
    /**
     * Sends a verification code via SMS to the specified phone number.
     *
     * @param string $phone the phone number to send the verification code to
     * @return array an array containing the SMS log details if the SMS was sent successfully
     * @throws Exception if the SMS failed to sync with SMS logs
     */
    #[ArrayShape(['id' => 'string', 'phone' => 'string', 'verify_code' => 'string', 'send_time' => 'string'])]
    public function send(string $phone): array
    {
        $code = Code::generateSmsVerifyCode();
        $smsLog = $this->createSmsLog($phone, $code);

        if ($smsLog->save()) {
            return $smsLog->toArray();
        }

        throw new BusinessException(ErrorCode::SMS_FAILED_TO_SYNC_SMS_LOGS);
    }

    /**
     * Check if a user is registered based on the phone number.
     *
     * @param string $phone the phone number of the user to check
     * @return bool returns true if the user is registered, false otherwise
     */
    public function isRegister(string $phone): bool
    {
        return SystemUser::query()->where(['phone' => $phone])->exists();
    }

    /**
     * Creates a new SMS log.
     *
     * @param string $phone the phone number to send the verification code to
     * @param string $code the verification code
     * @return SmsLog the newly created SMS log
     */
    protected function createSmsLog(string $phone, string $code): SmsLog
    {
        $smsLog = new SmsLog();
        $smsLog->phone = $phone;
        $smsLog->verify_code = $code;
        $smsLog->send_time = Carbon::now()->toDateTimeString();

        return $smsLog;
    }
}
