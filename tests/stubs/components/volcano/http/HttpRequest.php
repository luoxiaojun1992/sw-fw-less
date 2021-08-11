<?php

namespace SwFwLessTest\stubs\components\volcanoo\http;

use Psr\Http\Message\ResponseInterface;

class HttpRequest extends \SwFwLess\components\volcano\http\HttpRequest
{
    protected $mockResponse = [];

    public function next()
    {
        while (isset($this->mockResponse[$this->cursor])) {
            $response = $this->mockResponse[$this->cursor];
            ++$this->cursor;
            yield $response;
        }
    }

    public function addMockResponse(ResponseInterface $response)
    {
        $this->mockResponse[] = $response;
        return $this;
    }

    public function info()
    {
        $info = parent::info();
        $info['request_count'] = count($this->mockResponse);
        return $info;
    }
}
