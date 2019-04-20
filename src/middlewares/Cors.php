<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\Config;
use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;

class Cors extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @return \SwFwLess\components\http\Response|mixed
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
