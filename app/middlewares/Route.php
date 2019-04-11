<?php

namespace App\middlewares;

use App\components\http\Request;
use App\middlewares\traits\Parser;
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
        $controller = \App\facades\Container::get($controllerName);
        if ($controller instanceof \App\services\BaseService) {
            $controller->setRequest($appRequest);
        }
        $controller->setHandler($action)->setParameters($parameters);

        //Middleware
        $middlewareNames = config('middleware.routeMiddleware');
        if (isset($controllerAction[3])) {
            $middlewareNames = array_merge($middlewareNames, $controllerAction[3]);
        }
        /** @var \App\middlewares\MiddlewareContract[]|\App\middlewares\AbstractMiddleware[] $middlewareConcretes */
        $middlewareConcretes = [];
        foreach ($middlewareNames as $i => $middlewareName) {
            list($middlewareClass, $middlewareOptions) = $this->parseMiddlewareName($middlewareName);

            /** @var \App\middlewares\AbstractMiddleware $middlewareConcrete */
            $middlewareConcrete = \App\facades\Container::get($middlewareClass);
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
                return \App\components\http\Response::output(null, 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                return \App\components\http\Response::output(null, 405);
            case Dispatcher::FOUND:
                $this->setNext($this->getRequestHandler($request, $routeInfo));
                break;
            default:
                return \App\components\http\Response::output(null);
        }

        return $this->next();
    }
}
