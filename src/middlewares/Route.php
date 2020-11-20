<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\facades\Container;
use SwFwLess\middlewares\traits\Parser;
use FastRoute\Dispatcher;

class Route extends AbstractMiddleware
{
    static $cachedRouteInfo = [];

    use Parser;

    private function getRequestHandler(Request $appRequest, $routeInfo)
    {
        $controllerAction = $routeInfo[1];
        $route = $controllerAction[0];
        $appRequest->setRoute($route);
        $controllerName = $controllerAction[1];
        $action = $controllerAction[2];
        $parameters = $routeInfo[2];
        $routeDiSwitch = \SwFwLess\components\di\Container::diSwitch();
        $controller = $routeDiSwitch ? Container::make($controllerName) : new $controllerName;
        if ($controller instanceof \SwFwLess\services\BaseService) {
            $controller->setRequest($appRequest);
        }
        $controller->setHandler($action)->setParameters($parameters);

        //Middleware
        $middlewareNames = config('middleware.routeMiddleware');
        if (isset($controllerAction[3])) {
            $middlewareNames = array_merge($middlewareNames, $controllerAction[3]);
        }
        /** @var \SwFwLess\middlewares\MiddlewareContract[]|\SwFwLess\middlewares\AbstractMiddleware[] $middlewareConcretes */
        $middlewareConcretes = [];
        foreach ($middlewareNames as $i => $middlewareName) {
            list($middlewareClass, $middlewareOptions) = $this->parseMiddlewareName($middlewareName);

            /** @var \SwFwLess\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = $routeDiSwitch ?
                Container::make($middlewareClass) :
                new $middlewareClass;
            $middlewareConcrete->setParameters([$appRequest])->setOptions($middlewareOptions);
            if (isset($middlewareConcretes[$i - 1])) {
                $middlewareConcretes[$i - 1]->setNext($middlewareConcrete);
            }

            array_push($middlewareConcretes, $middlewareConcrete);
        }
        $middlewareConcretesCount = count($middlewareConcretes);
        if ($middlewareConcretesCount > 0) {
            $middlewareConcretes[$middlewareConcretesCount - 1]->setNext($controller);
        }
        array_push($middlewareConcretes, $controller);

        return $middlewareConcretes[0];
    }

    public function handle(Request $request)
    {
        $requestUri = $request->uri();
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $requestUri = rawurldecode($requestUri);

        $routeInfo = Scheduler::withoutPreemptive(function () use ($request, $requestUri) {
            $cacheKey = json_encode(['method' => $request->method(), 'uri' => $requestUri]);
            if (isset(self::$cachedRouteInfo[$cacheKey])) {
                $routeInfo = self::$cachedRouteInfo[$cacheKey];
            } else {
                /** @var Dispatcher $httpRouteDispatcher */
                $httpRouteDispatcher = $this->getOptions();
                self::$cachedRouteInfo[$cacheKey] = $routeInfo = $httpRouteDispatcher->dispatch(
                    $request->method(), $requestUri
                );
                if (count(self::$cachedRouteInfo) > 100) {
                    self::$cachedRouteInfo = array_slice(self::$cachedRouteInfo, 0, 100, true);
                }
            }
            return $routeInfo;
        });
        $routeResult = $routeInfo[0];
        switch ($routeResult) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                return Response::output('', 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
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
