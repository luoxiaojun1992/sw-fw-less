<?php

namespace SwFwLess\components\auth\jwt;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class JwtProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Jwt::create(config('auth.guards.jwt'));
    }
}
