<?php
/**
 * Created by PhpStorm.
 * User: webid
 * Date: 17-12-25
 * Time: 下午6:16
 */

namespace mmxs\tests;

use mmxs\Dysmsapi\Sms;
use PHPUnit\Framework\TestCase;

class TestSms extends TestCase
{
    public function test1()
    {
        $config = include __DIR__ . '/config.php';
        $sms    = new Sms($config, new EchoLogger());
        $rs     = $sms->send($config['phone'], $config['template_code'], ['code' => 1234]);
        $this->assertTrue($rs);
    }

    public function test2()
    {
        $config = include __DIR__ . '/config.php';
        $sms    = new Sms($config, new EchoLogger());
        $rs     = $sms->send('1761234538', '213', ['code' => 1234]);
        $this->assertTrue(!$rs);
    }
}
