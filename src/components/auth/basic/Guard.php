<?php

namespace SwFwLess\components\auth\basic;

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
        if (stripos($token, 'Bearer ') === 0) {
            $token = str_ireplace('Bearer ', '', $token);
        } elseif (stripos($token, 'Basic ') === 0) {
            $token = str_ireplace('Basic ', '', $token);
        }
        if (!$token) {
            return false;
        }

        $credential = base64_decode($token);
        if (!strpos($credential, ':')) {
            return false;
        }

        return (bool)$userProvider->retrieveByToken(...explode(':', $credential));
    }
}
