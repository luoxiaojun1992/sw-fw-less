<?php

class Request extends \SwFwLess\components\http\Request
{
    public $uri;

    public $method;

    public function __construct()
    {
        //
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function uri()
    {
        return $this->uri;
    }

    public function method()
    {
        return $this->method;
    }
}
