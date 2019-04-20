<?php

namespace SwFwLess\models;

use SwFwLess\components\auth\jwt\UserProviderContract;
use SwFwLess\facades\Jwt;

class JwtUser extends AbstractMysqlModel implements UserProviderContract
{
    public function retrieveByToken($authToken, $swfRequest = null)
    {
        $swfRequest = $swfRequest ?? request();

        if (!is_null($token = Jwt::validate($authToken, $swfRequest))) {
            $this->setPrimaryValue($token->getClaim(static::$primaryKey));
            return true;
        }

        return false;
    }
}
