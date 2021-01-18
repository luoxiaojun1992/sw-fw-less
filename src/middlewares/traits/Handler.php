<?php

namespace SwFwLess\middlewares\traits;

use Google\Protobuf\Internal\Message;
use SwFwLess\components\http\Response;
use SwFwLess\exceptions\HttpException;
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
            return $this->formatResponse(
                \SwFwLess\components\di\Container::routeDiSwitch() ?
                    Container::call([$this, $handler], $parameters) :
                    call_user_func_array([$this, $handler], $parameters)
            );
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            ObjectPool::release($this);
        }
    }

    protected function formatResponse($response)
    {
        if ($response instanceof Response) {
            return $response;
        }

        if (is_array($response)) {
            return Response::json($response);
        } elseif (is_string($response)) {
            return Response::output($response);
        } elseif ($response instanceof Message) {
            return $response;
        }

        throw new HttpException('Invalid http response', 500);
    }
}
