<?php

namespace App\middlewares;

use App\components\Config;
use App\components\http\Request;
use App\components\http\Response;

class Cors extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @return \App\components\http\Response|mixed
     */
    public function handle(Request $request)
    {
        if ($request->method() === 'OPTIONS' && Config::get('cors.switch')) {
            return Response::output('', 200, [
                'Access-Control-Allow-Origin' => (string)Config::get('cors.origin'),
            ]);
        }

        return $this->next();
    }
}
