<?php

namespace App\middlewares;

use App\components\http\Request;
use App\components\http\Response;
use App\middlewares\traits\Handler;

abstract class AbstractMiddleware implements MiddlewareContract
{
    use Handler;

    /**
     * @var MiddlewareContract
     */
    private $next;

    private $options;

    /**
     * @param Request $request
     * @return \App\components\http\Response
     */
    abstract public function handle(Request $request);

    /**
     * @return Response
     */
    public function next()
    {
        return $this->next->call();
    }

    /**
     * @param MiddlewareContract $middleware
     * @return $this
     */
    public function setNext(MiddlewareContract $middleware)
    {
        $this->next = $middleware;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}
