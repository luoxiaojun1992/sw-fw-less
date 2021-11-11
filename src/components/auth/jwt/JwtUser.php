<?php

namespace SwFwLess\components\auth\jwt;

use SwFwLess\facades\Jwt;
use SwFwLess\models\AbstractPDOModel;

class JwtUser extends AbstractPDOModel implements UserProviderContract
{
    public function retrieveByToken($authToken, $swfRequest = null)
    {
        $swfRequest = $swfRequest ?? \SwFwLess\components\functions\request();

        if (!is_null($token = Jwt::validate($authToken, $swfRequest))) {
            $this->setPrimaryValue($token->getClaim(static::$primaryKey));
            return true;
        }

        return false;
    }
}
