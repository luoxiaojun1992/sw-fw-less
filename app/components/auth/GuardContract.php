<?php

namespace App\components\auth;

interface GuardContract
{
    /**
     * @param $credentialCarrier
     * @param $credentialKey
     * @param $userProvider
     * @param $config
     * @return bool
     */
    public function validate($credentialCarrier, $credentialKey, $userProvider, $config);
}
