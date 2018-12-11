<?php

namespace App\components;

class Request
{
    private $swRequest;

    /**
     * @param $swRequest
     * @return $this
     */
    public function setSwRequest($swRequest)
    {
        $this->swRequest = $swRequest;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSwRequest()
    {
        return $this->swRequest;
    }

    public function param($name)
    {
        //todo
    }
}
