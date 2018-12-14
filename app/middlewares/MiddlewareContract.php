<?php

namespace App\middlewares;

use App\components\Request;

interface MiddlewareContract
{
    public function handle(Request $request);
}
