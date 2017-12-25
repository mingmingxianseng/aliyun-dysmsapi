<?php
/**
 * Created by PhpStorm.
 * User: webid
 * Date: 17-12-25
 * Time: 下午6:24
 */

namespace mmxs\tests;

use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        echo sprintf("[%s] %s . [%s]", $level, $message, var_export($context, true));
    }

}