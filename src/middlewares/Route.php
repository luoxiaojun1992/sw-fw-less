<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\facades\Container;
use SwFwLess\middlewares\traits\Parser;
use FastRoute\Dispatcher;

class Route extends AbstractMiddleware
{
    use Parser;

    private function getRequestHandler(Request $appRequest, $routeInfo)
    {
        $controllerAction = $routeInfo[1];
        $route = $controllerAction[0];
        $appRequest->setRoute($route);
        $controllerName = $controllerAction[1];
        $action = $controllerAction[2];
        $parameters = $routeInfo[2];
        $controller = config('route_di_switch') ? Container::make($controllerName) : new $controllerName;
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
            $middlewareConcrete = config('route_di_switch') ?
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

        /** @var Dispatcher $httpRouteDispatcher */
        $httpRouteDispatcher = $this->getOptions();
        $routeInfo = $httpRouteDispatcher->dispatch($request->method(), $requestUri);
        $routeResult = $routeInfo[0];
        switch ($routeResult) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                return \SwFwLess\components\http\Response::output('', 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                return \SwFwLess\components\http\Response::output('', 405);
            case Dispatcher::FOUND:
                $this->setNext($this->getRequestHandler($request, $routeInfo));
                break;
            default:
                return \SwFwLess\components\http\Response::output('');
        }

        return $this->next();
    }
}
