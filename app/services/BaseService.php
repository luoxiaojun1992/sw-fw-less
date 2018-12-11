<?php

namespace App\services;

use App\components\Request;

abstract class BaseService
{
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
