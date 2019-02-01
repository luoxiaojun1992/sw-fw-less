<?php

namespace App\middlewares;

use App\components\Config;
use App\components\Request;
use App\components\Response;
use App\facades\RateLimit;

class Throttle extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request)
    {
        if (!RateLimit::pass(...$this->parseConfig($request))) {
            return Response::output('', 429);
        }

        return $this->next($request);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function parseConfig(Request $request)
    {
        $throttleConfig = Config::get('throttle');
        if (is_callable($throttleConfig['metric'])) {
            $metric = call_user_func_array($throttleConfig['metric'], compact('request'));
        } else {
            $metric = $throttleConfig['metric'];
        }
        $period = $throttleConfig['period'];
        $throttle = $throttleConfig['throttle'];

        return [$metric, $period, $throttle];
    }
}
