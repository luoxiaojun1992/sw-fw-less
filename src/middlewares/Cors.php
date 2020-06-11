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
        $headers = [];
        if (Config::get('cors.switch')) {
            $headers['Access-Control-Allow-Origin'] = (string)Config::get('cors.origin');
        }

        if ($request->method() === 'OPTIONS') {
            return Response::output('', 200, $headers);
        }

        $response = $this->next();

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
