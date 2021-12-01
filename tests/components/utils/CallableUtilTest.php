<?php

namespace SwFwLessTest\components\utils;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\utils\CallableUtil;

class CallableUtilTest extends TestCase
{
    public function testGetName()
    {
        $callable = 'strlen';
        $this->assertEquals('strlen', CallableUtil::getName($callable));

        $callable = function (){};
        $this->assertEquals('Closure', CallableUtil::getName($callable));

        $callable = [new \stdClass(), 'test'];
        $this->assertEquals('stdClass', CallableUtil::getName($callable));

        $callable = ['\stdClass', 'test'];
        $this->assertEquals('\stdClass', CallableUtil::getName($callable));
    }
}
