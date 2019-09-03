<?php

namespace SwFwLess\components\auth\jwt;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class JwtProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Jwt::create(config('auth.guards.jwt'));
    }
}
