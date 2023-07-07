<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Test\Sys;

use App\Component\Code;
use CloudAdmin\Test\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class SmsTest extends HttpTestCase
{
    public function testGetSmsVerifyCode()
    {
        $res = $this->client->post('sys/sms/get-sms-verify-code', [
            'phone' => '123456',
        ]);
        $this->assertSame(0, $res['code']);
        $this->assertIsInt($res['data']['code']);
    }

    public function testSmsVerifyCode()
    {
        $this->assertIsString(Code::generateSmsVerifyCode(), 'this is string');
        $this->assertSame(6, strlen(Code::generateSmsVerifyCode()));
    }
}
