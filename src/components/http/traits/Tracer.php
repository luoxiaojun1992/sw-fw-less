<?php

namespace SwFwLess\components\http\traits;

use SwFwLess\components\swoole\Scheduler;

trait Tracer
{
    /**
     * @return \SwFwLess\components\zipkin\Tracer
     */
    public function getTracer()
    {
        return \SwFwLess\components\zipkin\Tracer::create($this);
    }
}
