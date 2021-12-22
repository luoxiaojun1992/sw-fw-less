<?php

namespace SwFwLessTests\components\utils\math;

use SwFwLess\components\utils\math\Math;

class MathBench
{
    public function benchNativeSum()
    {
        $arr = [];

        for ($i = 1; $i <= 1000000; ++$i) {
            $arr[] = $i;
        }

        array_sum($arr);
    }

    public function benchFFISum()
    {
        $mathUtil = \SwFwLess\components\utils\math\Math::create([
            'sum_ffi_min_count' => 1000000,
        ]);

        $arr = $mathUtil->createCNumbers(1000000);
        for ($i = 0; $i < 1000000; ++$i) {
            $arr[$i] = $i + 1;
        }

        $mathUtil->sum(null, 1000000, $arr);
    }
}
