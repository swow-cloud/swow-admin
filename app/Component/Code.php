<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Component;

use Exception;

use function random_int;
use function strlen;

class Code
{
    public static function generateSmsVerifyCode(int $length = 6): string
    {
        $charList = '0123456789';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            try {
                $randomChar = $charList[random_int(0, strlen($charList) - 1)];
            } catch (Exception $e) {
            }
            $code .= $randomChar;
        }
        return $code;
    }
}
