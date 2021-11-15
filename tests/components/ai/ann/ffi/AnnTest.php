<?php

namespace SwFwLessTest\components\ai\ann\ffi;

use PHPUnit\Framework\TestCase;
use SwFwLess\facades\Ann;

class AnnTest extends TestCase
{
    public function testTrainAndPredictOnce()
    {
        $cOutput = Ann::trainAndPredictOnce(
            [[0, 0], [0, 1], [1, 0], [1, 1]],
            [[0], [1], [1], [0]],
            4,
            [0, 1],
            2,
            1,
            2,
            1,
            3,
            1000
        );
        $this->assertGreaterThan(0, $cOutput[0]);
    }
}
