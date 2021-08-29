<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\facades\Container;

class ClosureMiddleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $handler = $this->getOptions();
        $parameters = [
            $request,
            [$this, 'next']
        ];

        return ((\SwFwLess\components\Config::get(
            'di_switch', \SwFwLess\components\di\Container::DEFAULT_DI_SWITCH)) &&
            (\SwFwLess\components\Config::get('route_di_switch'))) ?
            Container::call($handler, $parameters) :
            call_user_func_array($handler, $parameters);
    }
}
