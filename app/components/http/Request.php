<?php

namespace App\components\http;

use App\components\Helper;
use App\components\http\traits\Tracer;
use App\components\utils\swoole\coresource\traits\CoroutineRes;

class Request
{
    use Tracer;
    use CoroutineRes;

    /** @var \Swoole\Http\Request */
    private $swRequest;

    private $route;

    public function __construct()
    {
        static::register($this);
    }

    /**
     * @param \Swoole\Http\Request $swRequest
     * @return $this
     */
    public function setSwRequest(\Swoole\Http\Request $swRequest)
    {
        $this->swRequest = $swRequest;
        return $this;
    }

    /**
     * @return \Swoole\Http\Request
     */
    public function getSwRequest()
    {
        return $this->swRequest;
    }

    /**
     * @param mixed $route
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function get($name, $default = null)
    {
        return Helper::arrGet($this->getSwRequest()->get, $name, $default);
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function post($name, $default = null)
    {
        return Helper::arrGet($this->getSwRequest()->post, $name, $default);
    }

    /**
     * @param $name
     * @return null
     */
    public function file($name)
    {
        return Helper::arrGet($this->getSwRequest()->files, $name, null);
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function param($name, $default = null)
    {
        $getParam = $this->get($name, $default);
        if (isset($getParam)) {
            return $getParam;
        }

        $postParam = $this->post($name, $default);
        if (isset($postParam)) {
            return $postParam;
        }

        $fileParam = $this->file($name);
        if (isset($fileParam)) {
            return $fileParam;
        }

        return $default;
    }

    /**
     * @return array
     */
    public function all()
    {
        return array_merge((array)$this->getSwRequest()->get, (array)$this->getSwRequest()->post, (array)$this->getSwRequest()->files);
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function header($name, $default = null)
    {
        return Helper::arrGet($this->getSwRequest()->header, $name, $default);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name)
    {
        $headers = $this->getSwRequest()->header;
        return !is_null($headers) && array_key_exists($name, $headers);
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function server($name, $default = null)
    {
        $name = strtolower($name);
        return Helper::arrGet($this->getSwRequest()->server, $name, $default);
    }

    /**
     * @return string
     */
    public function method()
    {
        return strtoupper($this->server('request_method'));
    }

    /**
     * @return null
     */
    public function uri()
    {
        return $this->server('request_uri');
    }

    /**
     * @return null
     */
    public function queryString()
    {
        return $this->server('query_string');
    }

    /**
     * @return mixed
     */
    public function body()
    {
        return $this->getSwRequest()->rawcontent();
    }

    public function convertToPsr7()
    {
        $rawBody = $this->getSwRequest()->rawcontent();
        $contentType = $this->header('content-type');

        if (in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data']) && $this->method() === 'POST') {
            $parsedBody = $this->getSwRequest()->post;
        } else {
            if ($contentType === 'application/x-www-form-urlencoded') {
                parse_str((string) $rawBody, $parsedBody);
            } else {
                $parsedBody = null;
            }
        }

        return ServerRequestFactory::fromGlobals(
            $this->getSwRequest()->server ?? [],
            $this->getSwRequest()->get ?? [],
            $parsedBody ?? [],
            $this->getSwRequest()->cookie ?? [],
            $this->getSwRequest()->files ?? [],
            $this->getSwRequest()->header ?? [],
            $rawBody
        );
    }

    /**
     * @param $swRequest
     * @return Request
     */
    public static function fromSwRequest($swRequest)
    {
        return (new self())->setSwRequest($swRequest);
    }
}
