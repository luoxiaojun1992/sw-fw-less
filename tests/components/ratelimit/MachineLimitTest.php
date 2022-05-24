<?php

namespace SwFwLessTests\components\ratelimit;

class MachineLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testPass()
    {
        \SwFwLess\components\ratelimit\MemLimit::clearInstance();
        \SwFwLess\components\ratelimit\MemLimit::create([]);
        \SwFwLess\components\ratelimit\SysLoadLimit::clearInstance();
        \SwFwLess\components\ratelimit\SysLoadLimit::create([]);
        \SwFwLess\components\ratelimit\MachineLimit::clearInstance();
        \SwFwLess\components\ratelimit\MachineLimit::create([]);

        $passed = \SwFwLess\components\ratelimit\MachineLimit::create()->pass(
            'test', 60, 0, $remaining
        );
        $this->assertFalse($passed);
        $this->assertNull($remaining);

        $passed = \SwFwLess\components\ratelimit\MachineLimit::create()->pass(
            'test', 0, 0, $remaining
        );
        $this->assertFalse($passed);
        $this->assertNull($remaining);

        \SwFwLess\components\ratelimit\MachineLimit::create()->pass(
            'test', 0, 9999999999999999999999999999999999, $remaining
        );
        $this->assertNull($remaining);

        \SwFwLess\components\ratelimit\MachineLimit::create()->pass(
            'test', 100, 9999999999999999999999999999999999, $remaining
        );
        $this->assertNull($remaining);

        \SwFwLess\components\ratelimit\MachineLimit::create()->pass(
            'test', 500, 9999999999999999999999999999999999, $remaining
        );
        $this->assertNull($remaining);

        \SwFwLess\components\ratelimit\MachineLimit::clearInstance();
        \SwFwLess\components\ratelimit\SysLoadLimit::clearInstance();
        \SwFwLess\components\ratelimit\MemLimit::clearInstance();
    }
}
