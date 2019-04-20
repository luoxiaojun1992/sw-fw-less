<?php

namespace SwFwLess\services;

use SwFwLess\components\http\Request;
use SwFwLess\middlewares\MiddlewareContract;
use SwFwLess\middlewares\traits\Handler;

abstract class BaseService implements MiddlewareContract
{
    use Handler;

    /** @var Request */
    protected $request;

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
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
