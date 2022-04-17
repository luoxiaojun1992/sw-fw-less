<?php

class SysLoadLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testPass()
    {
        \SwFwLess\components\ratelimit\SysLoadLimit::clearInstance();
        \SwFwLess\components\ratelimit\SysLoadLimit::create([]);

        $passed = \SwFwLess\components\ratelimit\SysLoadLimit::create()->pass(
            'test', 60, 0, $remaining
        );
        $this->assertFalse($passed);
        $this->assertNull($remaining);

        $passed = \SwFwLess\components\ratelimit\SysLoadLimit::create()->pass(
            'test', 0, 0, $remaining
        );
        $this->assertFalse($passed);
        $this->assertNull($remaining);

        $passed = \SwFwLess\components\ratelimit\SysLoadLimit::create()->pass(
            'test', 0, 9999999999999999999999999999999999, $remaining
        );
        if ($passed) {
            $this->assertIsNumeric($remaining);
            $this->assertGreaterThan(0, $remaining);
        } else {
            $this->assertNull($remaining);
        }

        $passed = \SwFwLess\components\ratelimit\SysLoadLimit::create()->pass(
            'test', 100, 9999999999999999999999999999999999, $remaining
        );
        if ($passed) {
            $this->assertIsNumeric($remaining);
            $this->assertGreaterThan(0, $remaining);
        } else {
            $this->assertNull($remaining);
        }

        $passed = \SwFwLess\components\ratelimit\SysLoadLimit::create()->pass(
            'test', 500, 9999999999999999999999999999999999, $remaining
        );
        if ($passed) {
            $this->assertIsNumeric($remaining);
            $this->assertGreaterThan(0, $remaining);
        } else {
            $this->assertNull($remaining);
        }

        \SwFwLess\components\ratelimit\SysLoadLimit::clearInstance();
    }
}
