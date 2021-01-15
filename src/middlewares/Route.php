<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\utils\http\Url;
use SwFwLess\facades\Container;
use SwFwLess\facades\ObjectPool;
use SwFwLess\middlewares\traits\Parser;
use FastRoute\Dispatcher;
use SwFwLess\services\BaseService;
use SwFwLess\services\GrpcUnaryService;

class Route extends AbstractMiddleware
{
    static $routeCacheKeyCache = [];

    static $routeCacheKeyCacheCount = 0;

    static $cachedRouteInfo = [];

    static $cachedRouteInfoCount = 0;

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
        /** @var AbstractMiddleware|BaseService|GrpcUnaryService $controller */
        $controller = ObjectPool::pick($controllerName) ?:
            (
                $routeDiSwitch ?
                Container::make($controllerName) :
                new $controllerName
            );
        if ($controller instanceof \SwFwLess\services\BaseService) {
            $controller->setRequestAndHandlerAndParameters(
                $appRequest,
                $action,
                $parameters
            );
        } else {
            $controller->setHandlerAndParameters($action, $parameters);
        }

        //Middleware
        $middlewareNames = \SwFwLess\components\functions\config('middleware.routeMiddleware');
        if (isset($controllerAction[3])) {
            $middlewareNames = array_merge($middlewareNames, $controllerAction[3]);
        }
        $firstMiddlewareConcrete = null;
        $prevMiddlewareConcrete = null;
        foreach ($middlewareNames as $i => $middlewareName) {
            list($middlewareClass, $middlewareOptions) = \SwFwLess\middlewares\Parser::parseMiddlewareName(
                $middlewareName
            );

            /** @var \SwFwLess\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = ObjectPool::pick($middlewareClass) ?: ($routeDiSwitch ?
                Container::make($middlewareClass) :
                new $middlewareClass);

            if (is_null($firstMiddlewareConcrete)) {
                $firstMiddlewareConcrete = $middlewareConcrete;
            }

            $middlewareConcrete->setParametersAndOptions(
                [$appRequest],
                $middlewareOptions
            );
            if (!is_null($prevMiddlewareConcrete)) {
                $prevMiddlewareConcrete->setNext($middlewareConcrete);
            }
            $prevMiddlewareConcrete = $middlewareConcrete;
        }
        if (!is_null($prevMiddlewareConcrete)) {
            $prevMiddlewareConcrete->setNext($controller);
        }
        if (is_null($firstMiddlewareConcrete)) {
            $firstMiddlewareConcrete = $controller;
        }

        return $firstMiddlewareConcrete;
    }

    protected function selectRouteCacheKeyCache(
        $requestMethodFilter, $slicedJsonCache = [], $slicedJsonCacheCount = 0
    )
    {
        foreach (self::$routeCacheKeyCache as $cachedMethod => $cachedUriJson) {
            if ($requestMethodFilter($cachedMethod)) {
                foreach (array_reverse($cachedUriJson, true) as $cachedUri => $cachedJson) {
                    $slicedJsonCache[$cachedMethod][$cachedUri] = $cachedJson;
                    ++$slicedJsonCacheCount;
                    if ($slicedJsonCacheCount >= 100) {
                        break;
                    }
                }
            }
        }

        return [$slicedJsonCache, $slicedJsonCacheCount];
    }

    public function handle(Request $request)
    {
        $requestUri = $request->uri();
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $requestUri = Url::decode($requestUri);

        $routeInfo = Scheduler::withoutPreemptive(function () use ($request, $requestUri) {
            $requestMethod = $request->method();
            if (isset(self::$routeCacheKeyCache[$requestMethod][$requestUri])) {
                $cacheKey = self::$routeCacheKeyCache[$requestMethod][$requestUri];
            } else {
                $cacheKey = json_encode(['method' => $requestMethod, 'uri' => $requestUri]);
                self::$routeCacheKeyCache[$requestMethod][$requestUri] = $cacheKey;
                ++self::$routeCacheKeyCacheCount;
                if (self::$routeCacheKeyCacheCount > 100) {
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
                if (self::$cachedRouteInfoCount > 100) {
                    self::$cachedRouteInfo = array_slice(
                        self::$cachedRouteInfo, -100, null, true
                    );
                    self::$cachedRouteInfoCount = 100;
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

        return $this->next();
    }
}
