<?php

namespace SwFwLess\components\auth\jwt;

use SwFwLess\components\provider\AbstractProvider;

class JwtProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Jwt::create(\SwFwLess\components\functions\config('auth.guards.jwt'));
    }
}
