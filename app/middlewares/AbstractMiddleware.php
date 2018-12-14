<?php

namespace App\middlewares;

use App\components\Request;
use App\components\Response;

abstract class AbstractMiddleware implements MiddlewareContract
{
    private $next;

    private $isTerminal = false;

    private $terminalCallback;

    private $terminalCallbackVars;

    /**
     * @param Request $request
     * @return \App\components\Response
     */
    abstract public function handle(Request $request);

    /**
     * @param Request $request
     * @return Response
     */
    public function next(Request $request)
    {
        if (!$this->isTerminal) {
            return call_user_func_array([$this->next, 'handle'], [$request]);
        }

        return call_user_func_array($this->terminalCallback, $this->terminalCallbackVars);
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
     * @param $callback
     * @param $callbackVars
     * @return $this
     */
    public function terminal($callback, $callbackVars)
    {
        $this->isTerminal = true;
        $this->terminalCallback = $callback;
        $this->terminalCallbackVars = $callbackVars;
        return $this;
    }
}
