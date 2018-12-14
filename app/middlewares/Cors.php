<?php

namespace App\middlewares;

use App\components\Request;

class Cors extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        //todo add cors header

        return $this->next($request);
    }
}
