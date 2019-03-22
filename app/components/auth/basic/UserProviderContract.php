<?php

namespace App\components\auth\basic;

interface UserProviderContract
{
    public function retrieveByToken($user, $pwd);
}
