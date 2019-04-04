<?php

namespace App\components\auth\jwt;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class JwtProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Jwt::create(config('auth.guards.jwt'));
    }
}
