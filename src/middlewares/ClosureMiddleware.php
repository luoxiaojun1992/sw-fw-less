<?php

namespace SwFwLess\middlewares;

use SwFwLess\bootstrap\App;
use SwFwLess\components\http\Request;
use SwFwLess\facades\Container;

class ClosureMiddleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $handler = $this->getOptions();
        $next = function () {
            return $this->next();
        };
        $parameters = [$request, $next];

        return ((\SwFwLess\components\Config::get('di_switch', App::DEFAULT_DI_SWITCH)) &&
            (\SwFwLess\components\Config::get('route_di_switch'))) ?
            Container::call($handler, $parameters) :
            call_user_func_array($handler, $parameters);
    }
}
