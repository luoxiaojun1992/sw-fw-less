<?php

namespace App\components\auth\token;

interface UserProviderContract
{
    public function retrieveByToken($authToken);
}
