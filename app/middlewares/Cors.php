<?php

namespace App\middlewares;

use App\components\Config;
use App\components\Request;

class Cors extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @return \App\components\Response|mixed
     */
    public function handle(Request $request)
    {
        if (Config::get('cors.switch')) {
            return $this->next($request)->header('Access-Control-Allow-Origin', (string)Config::get('cors.origin'));
        }

        return $this->next($request);
    }
}
