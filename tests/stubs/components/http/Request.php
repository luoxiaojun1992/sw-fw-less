<?php

class Request extends \SwFwLess\components\http\Request
{
    public function __construct()
    {
        //
    }

    /**
     * @param $swRequest
     * @return Request
     */
    public static function fromSwRequest($swRequest)
    {
        $swfRequest = (new static());
        $swfRequest->swRequest = $swRequest;
        return $swfRequest;
    }
}
