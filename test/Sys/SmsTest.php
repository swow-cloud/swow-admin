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
use App\Service\SmsService;
use CloudAdmin\Test\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class SmsTest extends HttpTestCase
{
    public function testGetSmsVerifyCode()
    {
        $phone = '123456';
        $res = $this->client->post('sys/sms/get-verify-code', [
            'phone' => $phone,
        ]);
        $this->assertSame(0, $res['code']);
        $this->assertIsString($res['data']['verify_code']);
        $this->assertSame(6, strlen($res['data']['verify_code']));
    }

    public function testSmsVerifyCode()
    {
        $this->assertIsString(Code::generateSmsVerifyCode(), 'this is string');
        $this->assertSame(6, strlen(Code::generateSmsVerifyCode()));
    }

    public function testSmsIsRegister(){
        $service = \Hyperf\Support\make(SmsService::class);
        $this->assertTrue($service->isRegister('1'));
        $this->assertTrue($service->isRegister('2'));
    }
}
