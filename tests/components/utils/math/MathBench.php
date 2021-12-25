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
        $mathUtil = Math::create([
            'sum_ffi_min_count' => 1000000,
        ]);

        $arr = $mathUtil->createCNumbers(1000000);
        for ($i = 0; $i < 1000000; ++$i) {
            $arr[$i] = $i + 1;
        }

        $mathUtil->sum(null, 1000000, $arr);
    }

    public function benchNativeVectorAdd()
    {
        mt_srand(time());

        for ($i = 0; $i < 100000; ++$i) {
            mt_rand(10000, 99999) + mt_rand(10000, 99999);
        }
    }

    public function benchSimdVectorAdd()
    {
        $mathUtil = Math::create();

        $vector1 = $mathUtil->createCFloatNumbers(4);
        $vector2 = $mathUtil->createCFloatNumbers(4);

        mt_srand(time());

        for ($i = 0; $i < (100000 / 4); ++$i) {
            $vector1[0] = mt_rand(10000, 99999);
            $vector1[1] = mt_rand(10000, 99999);
            $vector1[2] = mt_rand(10000, 99999);
            $vector1[3] = mt_rand(10000, 99999);

            $vector2[0] = mt_rand(10000, 99999);
            $vector2[1] = mt_rand(10000, 99999);
            $vector2[2] = mt_rand(10000, 99999);
            $vector2[3] = mt_rand(10000, 99999);

            $mathUtil->vectorAdd($vector1, $vector2, 4);
        }
    }
}
