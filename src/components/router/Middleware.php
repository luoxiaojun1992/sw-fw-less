<?php

namespace SwFwLess\components\router;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\facades\Container;
use SwFwLess\middlewares\AbstractMiddleware;
use SwFwLess\middlewares\ClosureMiddleware;
use SwFwLess\middlewares\traits\Parser;
use FastRoute\Dispatcher;

class Middleware extends AbstractMiddleware
{
    /** @var Router */
    protected $router;

    use Parser;

    private function getRequestHandler(Request $appRequest)
    {
        $controller = $this->router->createController();

        $routeDiSwitch = \SwFwLess\components\di\Container::routeDiSwitch();
        $objectPool = ObjectPool::create();

        //Middleware
        $middlewareNames = \SwFwLess\components\functions\config('middleware.routeMiddleware');
        isset($controllerAction[3]) && ($middlewareNames = array_merge($middlewareNames, $controllerAction[3]));
        $firstMiddlewareConcrete = null;
        $prevMiddlewareConcrete = null;
        foreach ($middlewareNames as $middlewareName) {
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

    public function handle(Request $request)
    {
        $this->router = Router::create($request, $this->getOptions());

        $routeInfo = $this->router->parseRouteInfo();

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                return Response::output('', 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                return Response::output('', 405);
            case Dispatcher::FOUND:
                $this->setNext($this->getRequestHandler($request));
                break;
            default:
                return Response::output('');
        }

        //inline optimization, see static::next()
        return $this->next->call();
    }

    public function reset()
    {
        parent::reset();
        $this->router = null;
        return $this;
    }
}
