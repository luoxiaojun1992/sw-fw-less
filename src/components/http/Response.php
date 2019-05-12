<?php

namespace SwFwLess\components\http;

use SwFwLess\components\Helper;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;

class Response
{
    private $content;
    private $status = 200;
    private $reasonPhrase = '';
    private $protocolVersion = '1.1'; //Swoole not supported
    private $headers = [];
    private $trailers = [];

    /**
     * @param mixed $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param mixed $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $protocolVersion
     * @return $this
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    /**
     * @param $reasonPhrase
     * @return $this
     */
    public function setReasonPhrase($reasonPhrase)
    {
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function header($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function trailer($key, $value)
    {
        $this->trailers[$key] = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getTrailers()
    {
        return $this->trailers;
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function convertToPsr7()
    {
        $psrResponse = (new ResponseFactory())->createResponse($this->getStatus(), $this->getReasonPhrase())
            ->withProtocolVersion($this->getProtocolVersion());

        $headers = $this->getHeaders();
        foreach ($headers as $name => $value) {
            $psrResponse = $psrResponse->withAddedHeader($name, $value);
        }

        $body = $this->getContent();
        foreach ($psrResponse->getHeader('content-type') as $value) {
            if (substr($value, 0, 16) === 'application/grpc') {
                $body = '';
                break;
            }
        }

        $psrResponse->withBody((new StreamFactory())->createStream($body));

        return $psrResponse;
    }

    public function isServerError()
    {
        $statusCode = $this->getStatus();
        return $statusCode >= 500 && $statusCode < 600;
    }

    public function isClientError()
    {
        $statusCode = $this->getStatus();
        return $statusCode >= 400 && $statusCode < 500;
    }

    /**
     * @param $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function output($content, $status = 200, $headers = [])
    {
        return (new self)->setContent($content)->setStatus($status)->setHeaders($headers);
    }

    /**
     * @param $reply
     * @param int $status
     * @param array $headers
     * @param bool $toJson
     * @return Response
     */
    public static function grpc($reply, $status = 200, $headers = [], $toJson = false)
    {
        if ($toJson) {
            return static::json($reply->serializeToJsonString(), $status, $headers);
        } else {
            $headers['Content-Type'] = 'application/grpc+proto';
            $message = $reply->serializeToString();
            return static::output(pack('CN', 0, strlen($message)) . $message, $status, $headers);
        }
    }

    /**
     * @param $arr
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function json($arr, $status = 200, $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        $content = is_string($arr) ? $arr : Helper::jsonEncode($arr);
        return self::output($content, $status, $headers);
    }
}
