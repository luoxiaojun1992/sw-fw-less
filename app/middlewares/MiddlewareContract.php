<?php

namespace App\middlewares;

use App\components\Request;
use App\components\Response;

interface MiddlewareContract
{
    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request);
}
