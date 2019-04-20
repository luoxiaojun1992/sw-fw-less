<?php

namespace SwFwLess\components\auth\token;

use SwFwLess\components\auth\GuardContract;
use SwFwLess\components\http\Request;

class Guard implements GuardContract
{
    /**
     * @param Request $credentialCarrier
     * @param $tokenKey
     * @param UserProviderContract $userProvider
     * @param $config
     * @return bool
     */
    public function validate($credentialCarrier, $tokenKey, $userProvider, $config)
    {
        $token = $credentialCarrier->get($tokenKey) ?: $credentialCarrier->header(strtolower($tokenKey));
        return (bool)$userProvider->retrieveByToken($token);
    }
}
