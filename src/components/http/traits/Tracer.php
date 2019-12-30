<?php

namespace SwFwLess\components\http\traits;

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
