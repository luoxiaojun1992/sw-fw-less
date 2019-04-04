<?php

namespace App\facades;

use Lcobucci\JWT\Token;

/**
 * Class Jwt
 *
 * @method static Token issue($swfRequest = null, $payload = [])
 * @method static Token|null validate($tokenStr, $swfRequest = null)
 * @package App\facades
 */
class Jwt extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\auth\jwt\Jwt::create();
    }
}
