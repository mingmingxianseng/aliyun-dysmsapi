<?php
/**
 * Created by PhpStorm.
 * User: webid
 * Date: 17-12-25
 * Time: 下午6:16
 */

namespace mmxs\tests;

use mmxs\Dysmsapi\DysmsException;
use mmxs\Dysmsapi\Sms;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class TestSms extends TestCase
{
    public function test1()
    {
        $config = include __DIR__ . '/config.php';
        $sms    = new Sms($config, new EchoLogger());
        $data   = $sms->send($config['phone'], $config['template_code'], ['code' => 1234]);
        $this->assertTrue($data['Code'] == 'OK');
    }

    /**
     * test2
     *
     * @author chenmingming
     */
    public function test2()
    {
//        $this->expectException(DysmsException::class);
        $config           = include __DIR__ . '/config.php';
        $config['domain'] = 'www.baidu.com';
        $sms              = new Sms($config, new EchoLogger());
        try {
            $rs = $sms->send('17612345238', '213', ['code' => 1234]);
        } catch (DysmsException $e) {
            echo($e);
        }
        $this->assertTrue(true);

    }
}
