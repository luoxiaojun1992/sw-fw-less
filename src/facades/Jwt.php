<?php

namespace SwFwLess\facades;

use Lcobucci\JWT\Token;

/**
 * Class Jwt
 *
 * @method static Token issue($swfRequest = null, $payload = [])
 * @method static Token|null validate($tokenStr, $swfRequest = null)
 * @package SwFwLess\facades
 */
class Jwt extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\auth\jwt\Jwt::create();
    }
}
