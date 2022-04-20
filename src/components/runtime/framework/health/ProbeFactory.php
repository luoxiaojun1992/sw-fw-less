<?php

namespace SwFwLess\components\runtime\framework\health;

use SwFwLess\components\runtime\framework\health\probes\WorkerNumProbe;
use SwFwLess\components\support\factory\AbstractFactory;

class ProbeFactory extends AbstractFactory
{
    const PROBE_WORKER_NUM = 'WORKER_NUM';

    public static $resolvers = [
        self::PROBE_WORKER_NUM => [WorkerNumProbe::class, 'create'],
    ];
}
