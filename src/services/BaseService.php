<?php

namespace SwFwLess\services;

use SwFwLess\components\http\Request;
use SwFwLess\components\pool\Poolable;
use SwFwLess\middlewares\MiddlewareContract;
use SwFwLess\middlewares\traits\Handler;

abstract class BaseService implements MiddlewareContract, Poolable
{
    use Handler;

    /** @var Request */
    protected $request;

    private $releaseToPool = false;

    /**
     * @param $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param Request $request
     * @param string $handler
     * @param array $parameters
     * @return $this
     */
    public function setRequestAndHandlerAndParameters(Request $request, string $handler, array $parameters)
    {
        $this->request = $request;
        $this->handler = $handler;
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function reset()
    {
        $this->request = null;
        $this->handler = null;
        $this->setParameters([]);
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
}
