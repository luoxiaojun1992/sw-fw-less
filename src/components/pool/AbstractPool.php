<?php

namespace SwFwLess\components\pool;

use SwFwLess\components\swoole\Scheduler;

class AbstractPool
{
    protected function pickFromPool($pool)
    {
        return Scheduler::withoutPreemptive(function () use ($pool) {
            return array_pop($pool);
        });
    }
}
