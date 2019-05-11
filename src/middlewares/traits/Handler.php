<?php

namespace SwFwLess\middlewares\traits;

use SwFwLess\components\http\Response;
use SwFwLess\facades\Container;

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
        $response = Container::call([$this, $this->getHandler()], $this->getParameters());

        if (is_array($response)) {
            return Response::json($response);
        }

        return $response;
    }
}
