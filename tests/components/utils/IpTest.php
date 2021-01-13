<?php

class IpTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckIp()
    {
        $this->assertTrue(\SwFwLess\components\utils\Ip::checkIp(
            '172.19.10.1',
            '172.19.0.0/16'
        ));
    }
}
