<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\pool\Poolable;
use SwFwLess\middlewares\traits\Handler;

abstract class AbstractMiddleware implements MiddlewareContract, Poolable
{
    use Handler;

    /**
     * @var MiddlewareContract
     */
    protected $next;

    private $options;

    private $releaseToPool = false;

    /**
     * @param Request $request
     * @return \SwFwLess\components\http\Response
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

    /**
     * @param $parameters
     * @param $options
     * @return $this
     */
    public function setParametersAndOptions(array $parameters, $options)
    {
        $this->parameters = $parameters;
        $this->options = $options;
        return $this;
    }

    public function reset()
    {
        $this->next = null;
        $this->setOptions(null);
        $this->handler = null;
        $this->setParameters([]);
        $this->releaseToPool = false;
        return $this;
    }

    public function needRelease()
    {
        return $this->releaseToPool;
    }

    /**
     * @param bool $releaseToPool
     * @return $this
     */
    public function setReleaseToPool(bool $releaseToPool)
    {
        $this->releaseToPool = $releaseToPool;
        return $this;
    }

    public function getPoolResId()
    {
        return get_class($this);
    }

    public function refresh()
    {
        return $this;
    }
}
