<?php

namespace SwFwLessTest\stubs\components\volcanoo\http;

use Psr\Http\Message\ResponseInterface;
use SwFwLessTest\stubs\components\http\psr\PsrResponse;
use SwFwLessTest\stubs\components\http\psr\PsrStream;

class HttpRequest extends \SwFwLess\components\volcano\http\HttpRequest
{
    /** @var array ResponseInterface */
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
        $mockResponseInfo = [];
        foreach ($this->mockResponse as $mockResponse) {
            $mockResponseInfo[] = $mockResponse->getBody()->getContents();
        }
        $info['mock_response'] = $mockResponseInfo;
        return $info;
    }

    public static function getPsrResponse()
    {
        require_once __DIR__ . '/../../http/psr/PsrResponse.php';
        return new PsrResponse();
    }

    public static function getPsrStream()
    {
        require_once __DIR__ . '/../../http/psr/PsrStream.php';
        return new PsrStream();
    }

    public static function create($info = [])
    {
        $httpRequest = parent::create($info);
        if (isset($info['mock_response'])) {
            foreach ($info['mock_response'] as $mockResponseInfo) {
                $psrStream = static::getPsrStream();
                $psrStream->write($mockResponseInfo);
                $psrResponse = static::getPsrResponse();
                $psrResponse->withBody($psrStream);
                $httpRequest->addMockResponse($psrResponse);
            }
        }
        return $httpRequest;
    }
}
