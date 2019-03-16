<?php

namespace App\components\auth;

use App\components\auth\token\UserProviderContract;
use App\components\http\Request;

interface GuardContract
{
    /**
     * @param Request $request
     * @param $credentialKey
     * @param UserProviderContract $userProvider
     * @return bool
     */
    public function validate(Request $request, $credentialKey, UserProviderContract $userProvider);
}
