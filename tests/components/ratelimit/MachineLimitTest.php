<?php

class MemLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testPass()
    {
        \SwFwLess\components\ratelimit\MemLimit::clearInstance();
        \SwFwLess\components\ratelimit\MemLimit::create([]);

        $passed = \SwFwLess\components\ratelimit\MemLimit::create()->pass(
            'test', 60, 0, $remaining
        );
        $this->assertFalse($passed);
        $this->assertNull($remaining);

        $passed = \SwFwLess\components\ratelimit\MemLimit::create()->pass(
            'test', 0, 0, $remaining
        );
        $this->assertFalse($passed);
        $this->assertNull($remaining);

        $passed = \SwFwLess\components\ratelimit\MemLimit::create()->pass(
            'test', 0, 9999999999999999999999999999999999, $remaining
        );
        if ($passed) {
            $this->assertIsInt($remaining);
            $this->assertGreaterThan(0, $remaining);
        } else {
            $this->assertNull($remaining);
        }

        \SwFwLess\components\ratelimit\MemLimit::clearInstance();
    }
}
