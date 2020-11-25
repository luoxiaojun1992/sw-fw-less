<?php

namespace SwFwLess\middlewares\traits;

use SwFwLess\components\http\Response;
use SwFwLess\facades\Container;
use SwFwLess\facades\ObjectPool;

trait Handler
{
    private $handler;

    private $parameters = [];

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler ?? static::DEFAULT_HANDLER;
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
     * @return array
     */
    public function getHandlerAndParameters()
    {
        return [$this->getHandler(), $this->parameters];
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
     * @param string $handler
     * @param array $parameters
     * @return $this
     */
    public function setHandlerAndParameters(string $handler, array $parameters)
    {
        $this->handler = $handler;
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return array|mixed|Response
     * @throws \Throwable
     */
    public function call()
    {
        try {
            list($handler, $parameters) = $this->getHandlerAndParameters();
            $response = \SwFwLess\components\di\Container::routeDiSwitch() ?
                Container::call([$this, $handler], $parameters) :
                call_user_func_array([$this, $handler], $parameters);

            if (is_array($response)) {
                return Response::json($response);
            }

            return $response;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            ObjectPool::release($this);
        }
    }
}
