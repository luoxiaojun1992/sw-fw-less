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
        $this->mockSwooleScheduler();

        $mathUtil = \SwFwLess\components\utils\math\Math::create([
            'pool_size' => 1,
        ]);

        $testArr = range(1, 1000);
        $this->assertEquals(
            array_sum($testArr),
            $mathUtil->sum($testArr)
        );

        $testArr = range(1, 150000);
        $this->assertEquals(
            doubleval(array_sum($testArr)),
            $mathUtil->sum($testArr)
        );

        $mathUtil = \SwFwLess\components\utils\math\Math::create([
            'pool_size' => 1,
            'sum_ffi_min_count' => 200000,
        ]);

        $testArr = range(1, 300000);
        $this->assertEquals(
            doubleval(array_sum($testArr)),
            $mathUtil->sum($testArr)
        );
    }

    public function testVectorAdd()
    {
        $this->mockSwooleScheduler();

        $mathUtil = \SwFwLess\components\utils\math\Math::create([
            'pool_size' => 1,
        ]);

        $arrCount = 100000;

        $testArr1 = range(1, $arrCount);
        $testArr2 = range(1, $arrCount);
        $sum = [];

        $vector1 = $mathUtil->createCFloatNumbers(4);
        $vector2 = $mathUtil->createCFloatNumbers(4);

        for ($i = 0; $i < $arrCount; $i = $i + 4) {
            $vector1[0] = $testArr1[$i];
            $vector1[1] = $testArr1[$i + 1];
            $vector1[2] = $testArr1[$i + 2];
            $vector1[3] = $testArr1[$i + 3];

            $vector2[0] = $testArr2[$i];
            $vector2[1] = $testArr2[$i + 1];
            $vector2[2] = $testArr2[$i + 2];
            $vector2[3] = $testArr2[$i + 3];

            $sumVector = $mathUtil->vectorAdd($vector1, $vector2, 4);
            foreach ($sumVector as $elementSum) {
                $sum[] = $elementSum;
            }
        }

        foreach ($sum as $i => $value) {
            $this->assertEquals(floatval(2 * ($i + 1)), $value);
        }
    }

    public function testVectorSub()
    {
        $this->mockSwooleScheduler();

        $mathUtil = \SwFwLess\components\utils\math\Math::create([
            'pool_size' => 1,
        ]);

        $arrCount = 100000;

        $testArr1 = range(1, $arrCount);
        $testArr2 = range(1, $arrCount);
        $diff = [];

        $vector1 = $mathUtil->createCFloatNumbers(4);
        $vector2 = $mathUtil->createCFloatNumbers(4);

        for ($i = 0; $i < $arrCount; $i = $i + 4) {
            $vector1[0] = $testArr1[$i];
            $vector1[1] = $testArr1[$i + 1];
            $vector1[2] = $testArr1[$i + 2];
            $vector1[3] = $testArr1[$i + 3];

            $vector2[0] = $testArr2[$i];
            $vector2[1] = $testArr2[$i + 1];
            $vector2[2] = $testArr2[$i + 2];
            $vector2[3] = $testArr2[$i + 3];

            $diffVector = $mathUtil->vectorSub($vector1, $vector2, 4);
            foreach ($diffVector as $elementDiff) {
                $diff[] = $elementDiff;
            }
        }

        foreach ($diff as $i => $value) {
            $this->assertEquals(floatval(0), $value);
        }
    }
}
