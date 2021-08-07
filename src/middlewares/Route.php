<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\utils\http\Url;
use SwFwLess\facades\Container;
use SwFwLess\middlewares\traits\Parser;
use FastRoute\Dispatcher;
use SwFwLess\services\BaseService;
use SwFwLess\services\GrpcUnaryService;

class Route extends AbstractMiddleware
{
    static $routeCacheKeyCache = [];

    static $routeCacheKeyCacheCount = 0;

    static $routeCacheKeyCacheCapacity = 100;

    static $cachedRouteInfo = [];

    static $cachedRouteInfoCount = 0;

    static $cachedRouteInfoCapacity = 100;

    use Parser;

    private function getRequestHandler(Request $appRequest, $routeInfo)
    {
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
        ($controller instanceof \SwFwLess\services\BaseService) ? ($controller->setRequestAndHandlerAndParameters(
            $appRequest,
            $action,
            $parameters
        )) : ($controller->setHandlerAndParameters($action, $parameters));

        //Middleware
        $middlewareNames = \SwFwLess\components\functions\config('middleware.routeMiddleware');
        isset($controllerAction[3]) && ($middlewareNames = array_merge($middlewareNames, $controllerAction[3]));
        $firstMiddlewareConcrete = null;
        $prevMiddlewareConcrete = null;
        foreach ($middlewareNames as $i => $middlewareName) {
            $isClosureMiddleware = is_callable($middlewareName);

            list($middlewareClass, $middlewareOptions) = $isClosureMiddleware ?
                [ClosureMiddleware::class, $middlewareName] :
                \SwFwLess\middlewares\Parser::parseMiddlewareName(
                    $middlewareName
                );

            /** @var \SwFwLess\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = $objectPool->pick($middlewareClass) ?: ($routeDiSwitch ?
                Container::make($middlewareClass) :
                new $middlewareClass);

            $firstMiddlewareConcrete = $firstMiddlewareConcrete ?? $middlewareConcrete;

            $middlewareConcrete->setParametersAndOptions(
                [$appRequest],
                $middlewareOptions
            );
            ($prevMiddlewareConcrete !== null) && $prevMiddlewareConcrete->setNext($middlewareConcrete);
            $prevMiddlewareConcrete = $middlewareConcrete;
        }
        ($prevMiddlewareConcrete !== null) && $prevMiddlewareConcrete->setNext($controller);
        return $firstMiddlewareConcrete ?? $controller;
    }

    protected function selectRouteCacheKeyCache(
        $requestMethodFilter, $slicedJsonCache = [], $slicedJsonCacheCount = 0
    )
    {
        foreach (self::$routeCacheKeyCache as $cachedMethod => $cachedUriJson) {
            foreach ((
            $requestMethodFilter($cachedMethod) ?
                array_reverse($cachedUriJson, true) :
                []
            ) as $cachedUri => $cachedJson) {
                $slicedJsonCache[$cachedMethod][$cachedUri] = $cachedJson;
                ++$slicedJsonCacheCount;
                if ($slicedJsonCacheCount >= static::$routeCacheKeyCacheCapacity) {
                    break;
                }
            }
        }

        return [$slicedJsonCache, $slicedJsonCacheCount];
    }

    public function handle(Request $request)
    {
        $requestUri = $request->uri();
        $requestUri = Url::decode(
            (false !== ($pos = strpos($requestUri, '?'))) ?
                substr($requestUri, 0, $pos) :
                $requestUri
        );

        $routeInfo = Scheduler::withoutPreemptive(function () use ($request, $requestUri) {
            $requestMethod = $request->method();
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
                /** @var Dispatcher $httpRouteDispatcher */
                $httpRouteDispatcher = $this->getOptions();
                self::$cachedRouteInfo[$cacheKey] = $routeInfo = $httpRouteDispatcher->dispatch(
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
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                return Response::output('', 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                return Response::output('', 405);
            case Dispatcher::FOUND:
                $this->setNext($this->getRequestHandler($request, $routeInfo));
                break;
            default:
                return Response::output('');
        }

        //inline optimization, see static::next()
        return $this->next->call();
    }
}
