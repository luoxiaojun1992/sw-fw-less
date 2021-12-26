<?php

namespace SwFwLessTests\components\ai\ann\ffi;

use SwFwLess\facades\Ann;

class AnnBench
{
    public function benchTrainAndPredictOnce()
    {
        Ann::trainAndPredictOnce(
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
    }
}
