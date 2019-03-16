<?php

namespace App\models;

use App\components\auth\token\UserProviderContract;

class User extends AbstractMysqlModel implements UserProviderContract
{
    public function retrieveByToken($authToken)
    {
        //demo
        $this->id = 1;
        $this->name = 'test';

        return $this;
    }
}
