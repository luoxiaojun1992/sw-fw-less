<?php

namespace App\middlewares\traits;

trait Handler
{
    private $handler = 'handle';

    private $parameters = [];

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @param string $handler
     * @return $this
     */
    public function setHandler(string $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        return call_user_func_array([$this, $this->getHandler()], $this->getParameters());
    }
}
