<?php

namespace SwFwLess\components\auth\basic;

interface UserProviderContract
{
    public function retrieveByToken($user, $pwd);
}
