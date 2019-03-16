<?php

namespace App\components\auth\token;

use App\components\http\Request;

class Guard
{
    /**
     * @param Request $request
     * @param $tokenKey
     * @param UserProviderContract $userProvider
     * @return bool
     */
    public function validate(Request $request, $tokenKey, UserProviderContract $userProvider)
    {
        $token = $request->get($tokenKey) ?: $request->header($tokenKey);
        return $token === $userProvider->retrieveByToken($token);
    }
}
