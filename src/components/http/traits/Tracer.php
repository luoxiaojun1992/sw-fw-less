<?php

namespace SwFwLess\components\http\traits;

trait Tracer
{
    protected $tracer;

    /**
     * @return \SwFwLess\components\zipkin\Tracer
     */
    public function getTracer()
    {
        if (!is_null($this->tracer)) {
            return $this->tracer;
        }

        return $this->tracer = new \SwFwLess\components\zipkin\Tracer($this);
    }
}
