<?php

use Mockery as M;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        \SwFwLess\components\Config::clear();
    }

    protected function mockSwooleScheduler()
    {
        $mockScheduler = M::mock('alias:' . 'SwFwLess\components\swoole\Scheduler');
        $mockScheduler->shouldReceive('withoutPreemptive')
            ->with(M::type('callable'))
            ->andReturnUsing(function ($arg) {
                return call_user_func($arg);
            });
    }

    public function testSetAndGet()
    {
        $this->mockSwooleScheduler();

        \SwFwLess\components\Config::initByArr([
            'foo' => 'bar'
        ]);

        $this->assertEquals(
            'bar',
            \SwFwLess\components\Config::get('foo')
        );
    }
}
