<?php

namespace SwFwLess\components\auth\jwt;

interface UserProviderContract
{
    public function retrieveByToken($jwtToken, $swfRequest = null);
}
