<?php

namespace SwFwLess\components\router;

use FastRoute\Dispatcher;
use SwFwLess\components\functions;
use SwFwLess\components\http\Request;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\pool\Poolable;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\utils\data\structure\Arr;
use SwFwLess\components\utils\http\Url;
use SwFwLess\facades\Container;
use SwFwLess\middlewares\AbstractMiddleware;
use SwFwLess\services\BaseService;
use SwFwLess\services\GrpcUnaryService;
use SwFwLess\services\internals\DatetimeService;

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

    public static function clearRouteCache()
    {
        static::$routeCacheKeyCache = [];
        static::$routeCacheKeyCacheCount = 0;
        static::$routeCacheKeyCacheCapacity = 100;
        static::$cachedRouteInfo = [];
        static::$cachedRouteInfoCount = 0;
        static::$cachedRouteInfoCapacity = 100;
    }

    public static function createDispatcher()
    {
        return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $routerConfig = functions\config('router');
            if (Arr::isArrayElement($routerConfig, 'single')) {
                foreach ($routerConfig['single'] as $router) {
                    array_unshift($router[2], $router[1]);
                    $r->addRoute($router[0], $router[1], $router[2]);
                }
            }
            if (Arr::isArrayElement($routerConfig, 'resource')) {
                foreach ($routerConfig['resource'] as $router) {
                    $resourceMethodMap = [
                        'index' => 'GET',
                        'show' => 'GET',
                        'store' => 'POST',
                        'update' => 'PUT',
                        'destroy' => 'DELETE',
                    ];
                    foreach (array_keys($resourceMethodMap) as $resourceMethod) {
                        if (Arr::safeInArray($resourceMethod, ['index', 'store'])) {
                            $resourceRequestUri = $router[0];
                        } else {
                            $resourceRequestUri = rtrim($router[0], '/') . '/{id}';
                        }
                        $resourceReqMethod = $resourceMethodMap[$resourceMethod];
                        $r->addRoute(
                            $resourceReqMethod, $resourceRequestUri,
                            [$resourceRequestUri, $router[1], $resourceMethod, $router[2] ?? []]
                        );
                    }
                }
            }
            if (Arr::isArrayElement($routerConfig, 'grpc')) {
                foreach ($routerConfig['grpc'] as $package => $services) {
                    foreach ($services as $service => $procedures) {
                        foreach ($procedures as $procedure => $router) {
                            $grpcRequestUri = '/' . trim($package, '/') .
                                '.' . trim($service, '/') .
                                '/' . trim($procedure, '/');
                            $r->addRoute(
                                'POST', $grpcRequestUri,
                                [$grpcRequestUri, $router[0], $procedure, $router[1] ?? []]
                            );
                        }
                    }
                }
            }
            if (Arr::isArrayElement($routerConfig, 'group')) {
                foreach ($routerConfig['group'] as $prefix => $routers) {
                    $r->addGroup($prefix, function (\FastRoute\RouteCollector $r) use ($routers, $prefix) {
                        foreach ($routers as $router) {
                            array_unshift(
                                $router[2], '/' . trim($prefix, '/') .
                                '/' . trim($router[1], '/')
                            );
                            $r->addRoute($router[0], $router[1], $router[2]);
                        }
                    });
                }
            }
            $r->addGroup('/internal', function (\FastRoute\RouteCollector $r) {
                $r->addGroup('/monitor', function (\FastRoute\RouteCollector $r) {
                    if (functions\config('monitor.switch')) {
                        $r->addRoute(
                            'GET',
                            '/pool',
                            ['/internal/monitor/pool', \SwFwLess\services\internals\MonitorService::class, 'pool']
                        );
                        $r->addRoute(
                            'GET',
                            '/swoole',
                            ['/internal/monitor/swoole', \SwFwLess\services\internals\MonitorService::class, 'swoole']
                        );
                        $r->addRoute(
                            'GET',
                            '/memory',
                            ['/internal/monitor/memory', \SwFwLess\services\internals\MonitorService::class, 'memory']
                        );
                        $r->addRoute(
                            'GET',
                            '/cpu',
                            ['/internal/monitor/cpu', \SwFwLess\services\internals\MonitorService::class, 'cpu']
                        );
                        $r->addRoute(
                            'GET',
                            '/status',
                            ['/internal/monitor/status', \SwFwLess\services\internals\MonitorService::class, 'status']
                        );
                    }
                });
                if (functions\config('log.switch')) {
                    $r->addRoute(
                        'GET',
                        '/log/flush',
                        ['/internal/log/flush', \SwFwLess\services\internals\LogService::class, 'flush']
                    );
                }
                if (functions\config('chaos.switch', false)) {
                    $r->addGroup('/chaos', function (\FastRoute\RouteCollector $r) {
                        $r->addGroup('/fault', function (\FastRoute\RouteCollector $r) {
                            $r->addRoute(
                                'POST',
                                '/{id}',
                                [
                                    '/internal/chaos/fault/{id}', \SwFwLess\services\internals\ChaosService::class,
                                    'injectFault',
                                ]
                            );
                            $r->addRoute(
                                'GET',
                                '/{id}',
                                [
                                    '/internal/chaos/fault/{id}', \SwFwLess\services\internals\ChaosService::class,
                                    'fetchFault',
                                ]
                            );
                        });
                    });
                }
                if (functions\config('time_api_switch', false)) {
                    $r->addRoute(
                        'GET',
                        '/time-api',
                        [
                            '/internal/time-api',
                            DatetimeService::class,
                            'timestamp',
                        ]
                    );
                }
            });
        });
    }

    public static function create(Request $appRequest, Dispatcher $dispatcher)
    {
        $router = ObjectPool::create()->pick(static::class) ?: new static($appRequest, $dispatcher);
        $router->appRequest = $appRequest;
        $router->dispatcher = $dispatcher;
        return $router;
    }

    public function __construct(?Request $appRequest = null, ?Dispatcher $dispatcher = null)
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
        return ($controller instanceof \SwFwLess\services\BaseService) ?
            ($controller->setRequestAndHandlerAndParameters(
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
                        self::$cachedRouteInfo, -1 * static::$cachedRouteInfoCapacity, null,
                        true
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
