<?php

use Mockery as M;

class MathTest extends \PHPUnit\Framework\TestCase
{
    protected function mockSwooleScheduler()
    {
        $mockScheduler = M::mock('alias:' . 'SwFwLess\components\swoole\Scheduler');
        $mockScheduler->shouldReceive('withoutPreemptive')
            ->with(M::type('callable'))
            ->andReturnUsing(function ($arg) {
                return call_user_func($arg);
            });
    }

    public function testSum()
    {
        //TODO
        $this->mockSwooleScheduler();

        $mathUtil = \SwFwLess\components\utils\math\Math::create([
            'pool_size' => 1,
        ]);

        $testArr = range(1, 1000);
        $this->assertEquals(
            array_sum($testArr),
            $mathUtil->sum($testArr)
        );

        $testArr = range(1, 100000);
        $this->assertEquals(
            doubleval(array_sum($testArr)),
            $mathUtil->sum($testArr)
        );
    }
}
