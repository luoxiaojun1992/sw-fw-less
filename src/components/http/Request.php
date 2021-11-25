<?php

namespace SwFwLess\components\http;

use SwFwLess\components\Helper;
use SwFwLess\components\http\traits\Tracer;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\pool\Poolable;
use SwFwLess\components\swoole\coresource\traits\CoroutineRes;
use SwFwLess\components\utils\runtime\Swoole;
use Swoole\Coroutine;

class Request implements Poolable
{
    use Tracer;
    use CoroutineRes;

    /** @var \Swoole\Http\Request */
    protected $swRequest;

    private $route;

    private $cid;

    private $releaseToPool = false;

    public function __construct()
    {
        static::register($this);

        $this->cid = Coroutine::getCid();
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
     * @return mixed
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param null $name
     * @param null $default
     * @return string|array|null
     */
    public function get($name = null, $default = null)
    {
        return is_null($name) ?
            (($this->getSwRequest()->get) ?? []) :
            Helper::arrGet($this->getSwRequest()->get, $name, $default);
    }

    /**
     * @param $getParams
     * @return $this
     */
    public function setAllGet($getParams)
    {
        $this->getSwRequest()->get = $getParams;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setGet($name, $value)
    {
        Helper::arrSet($this->getSwRequest()->get, $name, $value);
        return $this;
    }

    /**
     * @param null $name
     * @param null $default
     * @return null
     */
    public function post($name = null, $default = null)
    {
        return is_null($name) ?
            (($this->getSwRequest()->post) ?? []) :
            Helper::arrGet($this->getSwRequest()->post, $name, $default);
    }

    /**
     * @param $postParams
     * @return $this
     */
    public function setAllPost($postParams)
    {
        $this->getSwRequest()->post = $postParams;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setPost($name, $value)
    {
        Helper::arrSet($this->getSwRequest()->post, $name, $value);
        return $this;
    }

    /**
     * @param null $name
     * @param null $default
     * @return null
     */
    public function file($name = null, $default = null)
    {
        return is_null($name) ?
            (($this->getSwRequest()->files) ?? []) :
            Helper::arrGet($this->getSwRequest()->files, $name, $default);
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
        return array_merge($this->get(), $this->post(), $this->file());
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function header($name, $default = null)
    {
        $name = strtolower($name);
        return Helper::arrGet($this->getSwRequest()->header, $name, $default);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name)
    {
        $name = strtolower($name);
        $headers = $this->getSwRequest()->header;
        return !is_null($headers) && array_key_exists($name, $headers);
    }

    public function realIp($prior = 'x-real-ip')
    {
        $prior = strtolower($prior);
        if ($this->hasHeader($prior)) {
            return $this->header($prior);
        } elseif ($this->hasServer($prior)) {
            return $this->server($prior);
        } elseif ($prior !== 'x-forwarded-for' && $this->hasHeader('x-forwarded-for')) {
            return trim(explode(',', $this->header('x-forwarded-for'))[0]);
        } elseif ($prior !== 'remote_addr') {
            return $this->server('remote_addr');
        } else {
            return null;
        }
    }

    public function isIpV6()
    {
        return substr_count($this->realIp(), ':') > 1;
    }

    public function userPort()
    {
        $remotePort = $this->server('REMOTE_PORT');
        return (!is_null($remotePort)) ? ((int)$remotePort) : null;
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
     * @param $name
     * @return bool
     */
    public function hasServer($name)
    {
        $name = strtolower($name);
        $servers = $this->getSwRequest()->server;
        return !is_null($servers) && array_key_exists($name, $servers);
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

    /**
     * @return bool
     */
    public function isGrpc()
    {
        return substr($this->header('content-type'), 0, 16) === 'application/grpc';
    }

    /**
     * @return bool
     */
    public function isGrpcJson()
    {
        return substr($this->header('content-type'), 0, 21) === 'application/grpc+json';
    }

    /**
     * @return bool
     */
    public function isJson()
    {
        return substr($this->header('content-type'), 0, 16) === 'application/json';
    }

    /**
     * @return bool
     */
    public function isHttp2()
    {
        return substr($this->server('server_protocol'), 0, 6) === 'HTTP/2';
    }

    public function convertToPsr7()
    {
        $rawBody = null;
        $parsedBody = null;

        if (!$this->isGrpc()) {
            $rawBody = $this->getSwRequest()->rawcontent();
            $contentType = $this->header('content-type');

            if ((substr($contentType, 0, 33) === 'application/x-www-form-urlencoded' ||
                    substr($contentType, 0, 19) === 'multipart/form-data') &&
                $this->method() === 'POST'
            ) {
                $parsedBody = $this->getSwRequest()->post;
            } else {
                if (substr($contentType, 0, 33) === 'application/x-www-form-urlencoded') {
                    parse_str((string)$rawBody, $parsedBody);
                }
            }
        }

        return ServerRequestFactory::fromGlobals(
            $this->getSwRequest()->server ?? [],
            $this->get(),
            $parsedBody ?? [],
            $this->getSwRequest()->cookie ?? [],
            $this->file(),
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
        /** @var static $swfRequest */
        $swfRequest = ObjectPool::create()->pick(static::class);
        if ($swfRequest) {
            $cid = $swfRequest->cid;
            ($cid !== null) && static::release($cid, false);
            static::register($swfRequest);
            $swfRequest->cid = Coroutine::getCid();
        } else {
            $swfRequest = new static();
        }
        $swfRequest->swRequest = $swRequest;
        return $swfRequest;
    }

    public function reset()
    {
        $this->swRequest = null;
        $this->route = null;
        $this->cid = null;
        $this->releaseToPool = false;
    }

    public function needRelease()
    {
        return $this->releaseToPool;
    }

    public function setReleaseToPool(bool $releaseToPool)
    {
        $this->releaseToPool = $releaseToPool;
        return $this;
    }

    public function getPoolResId()
    {
        return get_class($this);
    }

    public function refresh()
    {
        return $this;
    }
}
