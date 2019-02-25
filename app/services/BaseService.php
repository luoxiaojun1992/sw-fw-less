<?php

namespace App\services;

use App\components\Request;
use App\middlewares\MiddlewareContract;
use App\middlewares\traits\Handler;

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
