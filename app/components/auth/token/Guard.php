<?php

namespace App\components\auth\token;

use App\components\auth\GuardContract;
use App\components\http\Request;

class Guard implements GuardContract
{
    /**
     * @param Request $credentialCarrier
     * @param $tokenKey
     * @param UserProviderContract $userProvider
     * @return bool
     */
    public function validate($credentialCarrier, $tokenKey, $userProvider)
    {
        $token = $credentialCarrier->get($tokenKey) ?: $credentialCarrier->header($tokenKey);
        return $token === $userProvider->retrieveByToken($token);
    }
}
