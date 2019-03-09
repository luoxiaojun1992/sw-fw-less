<?php

namespace App\components\http\traits;

trait Tracer
{
    protected $tracer;

    /**
     * @return mixed
     */
    public function getTracer()
    {
        if (!is_null($this->tracer)) {
            return $this->tracer;
        }

        return $this->tracer = (new \App\components\zipkin\Tracer())->setRequest($this);
    }
}
