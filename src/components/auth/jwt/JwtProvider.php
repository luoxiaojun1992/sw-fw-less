<?php

namespace SwFwLess\components\auth\jwt;

use SwFwLess\components\provider\WorkerProviderContract;

class JwtProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        Jwt::create(\SwFwLess\components\functions\config('auth.guards.jwt'));
    }

    public static function shutdownWorker()
    {
        //
    }
}
