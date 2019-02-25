<?php

namespace App\middlewares;

use App\components\Request;
use App\components\Response;
use App\middlewares\traits\Handler;

abstract class AbstractMiddleware implements MiddlewareContract
{
    use Handler;

    /**
     * @var MiddlewareContract
     */
    private $next;

    /**
     * @param Request $request
     * @return \App\components\Response
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
}
