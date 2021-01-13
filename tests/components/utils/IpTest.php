<?php

class IpTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckIp()
    {
        $this->assertTrue(\SwFwLess\components\utils\Ip::checkIp(
            '172.19.10.1',
            '172.19.0.0/16'
        ));

        $this->assertTrue(\SwFwLess\components\utils\Ip::checkIp(
            '172.19.10.1',
            '192.168.0.1/0'
        ));

        $this->assertTrue(\SwFwLess\components\utils\Ip::checkIp(
            '172.19.10.1',
            '172.19.10.1'
        ));

        $this->assertFalse(\SwFwLess\components\utils\Ip::checkIp(
            '172.20.10.1',
            '172.19.0.0/16'
        ));

        $this->assertFalse(\SwFwLess\components\utils\Ip::checkIp(
            '172.19.10.1',
            '192.168.0.1.1/0'
        ));

        $this->assertFalse(\SwFwLess\components\utils\Ip::checkIp(
            '172.19.10.2',
            '172.19.10.1'
        ));
    }
}
