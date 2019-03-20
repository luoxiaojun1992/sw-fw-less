<?php

namespace App\components\auth\jwt;

use App\components\auth\GuardContract;
use App\components\http\Request;

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
        return (bool)$userProvider->retrieveByToken($token, $config['sign_key'], $config['jid']);
    }
}
