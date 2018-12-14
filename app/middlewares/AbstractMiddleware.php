<?php

namespace App\middlewares;

use App\components\Request;

abstract class AbstractMiddleware implements MiddlewareContract
{
    private $next;

    private $isTerminal = false;

    private $terminalCallback;

    private $terminalCallbackVars;

    abstract public function handle(Request $request);

    public function next(Request $request)
    {
        if (!$this->isTerminal) {
            return call_user_func_array([$this->next, 'handle'], [$request]);
        }

        return call_user_func_array($this->terminalCallback, $this->terminalCallbackVars);
    }

    public function setNext(MiddlewareContract $middleware)
    {
        $this->next = $middleware;
        return $this;
    }

    public function terminal($callback, $callbackVars)
    {
        $this->isTerminal = true;
        $this->terminalCallback = $callback;
        $this->terminalCallbackVars = $callbackVars;
        return $this;
    }
}
