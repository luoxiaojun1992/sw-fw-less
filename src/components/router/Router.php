<?php

namespace SwFwLess\components\router;

use FastRoute\Dispatcher;
use SwFwLess\components\http\Request;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\pool\Poolable;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\utils\http\Url;
use SwFwLess\facades\Container;
use SwFwLess\middlewares\AbstractMiddleware;
use SwFwLess\services\BaseService;
use SwFwLess\services\GrpcUnaryService;

class Router implements Poolable
{
    static $routeCacheKeyCache = [];

    static $routeCacheKeyCacheCount = 0;

    static $routeCacheKeyCacheCapacity = 100;

    static $cachedRouteInfo = [];

    static $cachedRouteInfoCount = 0;

    static $cachedRouteInfoCapacity = 100;

    protected $appRequest;

    protected $dispatcher;

    protected $routerInfo;

    private $releaseToPool = false;

    public static function create(Request $appRequest, Dispatcher $dispatcher)
    {
        return ObjectPool::create()->pick(static::class) ?: new static($appRequest, $dispatcher);
    }

    public function __construct(Request $appRequest, Dispatcher $dispatcher)
    {
        $this->appRequest = $appRequest;
        $this->dispatcher = $dispatcher;
    }

    public function createController()
    {
        $routeInfo = $this->routerInfo;
        $appRequest = $this->appRequest;
        $controllerAction = $routeInfo[1];
        $route = $controllerAction[0];
        $appRequest->setRoute($route);
        $controllerName = $controllerAction[1];
        $action = $controllerAction[2];
        $parameters = $routeInfo[2];
        $routeDiSwitch = \SwFwLess\components\di\Container::routeDiSwitch();
        $objectPool = ObjectPool::create();
        /** @var AbstractMiddleware|BaseService|GrpcUnaryService $controller */
        $controller = $objectPool->pick($controllerName) ?:
            (
            $routeDiSwitch ?
                Container::make($controllerName) :
                new $controllerName
            );
        return ($controller instanceof \SwFwLess\services\BaseService) ? ($controller->setRequestAndHandlerAndParameters(
            $appRequest,
            $action,
            $parameters
        )) : ($controller->setHandlerAndParameters($action, $parameters));
    }

    public function parseRouteInfo()
    {
        $appRequest = $this->appRequest;
        $requestUri = $appRequest->uri();
        $requestUri = Url::decode(
            (false !== ($pos = strpos($requestUri, '?'))) ?
                substr($requestUri, 0, $pos) :
                $requestUri
        );

        $routeInfo = Scheduler::withoutPreemptive(function () use ($appRequest, $requestUri) {
            $requestMethod = $appRequest->method();
            if (isset(self::$routeCacheKeyCache[$requestMethod][$requestUri])) {
                $cacheKey = self::$routeCacheKeyCache[$requestMethod][$requestUri];
            } else {
                $cacheKey = json_encode(['method' => $requestMethod, 'uri' => $requestUri]);
                self::$routeCacheKeyCache[$requestMethod][$requestUri] = $cacheKey;
                ++self::$routeCacheKeyCacheCount;
                if (self::$routeCacheKeyCacheCount > static::$routeCacheKeyCacheCapacity) {
                    list($slicedJsonCache, $slicedJsonCacheCount) = $this->selectRouteCacheKeyCache(
                        function ($cachedRequestMethod) use ($requestMethod) {
                            return $cachedRequestMethod === $requestMethod;
                        }
                    );
                    list($slicedJsonCache, $slicedJsonCacheCount) = $this->selectRouteCacheKeyCache(
                        function ($cachedRequestMethod) use ($requestMethod) {
                            return $cachedRequestMethod !== $requestMethod;
                        },
                        $slicedJsonCache,
                        $slicedJsonCacheCount
                    );
                    self::$routeCacheKeyCache = $slicedJsonCache;
                    self::$routeCacheKeyCacheCount = $slicedJsonCacheCount;
                }
            }

            if (isset(self::$cachedRouteInfo[$cacheKey])) {
                $routeInfo = self::$cachedRouteInfo[$cacheKey];
            } else {
                self::$cachedRouteInfo[$cacheKey] = $routeInfo = $this->dispatcher->dispatch(
                    $requestMethod, $requestUri
                );
                ++self::$cachedRouteInfoCount;
                if (self::$cachedRouteInfoCount > static::$cachedRouteInfoCapacity) {
                    self::$cachedRouteInfo = array_slice(
                        self::$cachedRouteInfo, -1 * static::$cachedRouteInfoCapacity, null, true
                    );
                    self::$cachedRouteInfoCount = static::$cachedRouteInfoCapacity;
                }
            }
            return $routeInfo;
        });

        $this->routerInfo = $routeInfo;
        return $routeInfo;
    }

    public function refresh()
    {
        return $this;
    }

    public function reset()
    {
        $this->appRequest = null;
        $this->dispatcher = null;
        $this->routerInfo = null;
        $this->releaseToPool = false;
        return $this;
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
}
