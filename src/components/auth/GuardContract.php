<?php

namespace SwFwLess\components\auth;

interface GuardContract
{
    public function __construct($config = []);

    /**
     * @param $credentialCarrier
     * @param $credentialKey
     * @param $userProvider
     * @param $config
     * @return bool
     */
    public function validate($credentialCarrier, $credentialKey, $userProvider, $config);
}
