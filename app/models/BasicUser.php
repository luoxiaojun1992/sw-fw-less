<?php

namespace App\models;

use App\components\auth\basic\UserProviderContract;

class BasicUser extends AbstractMysqlModel implements UserProviderContract
{
    public function retrieveByToken($user, $pwd)
    {
        $result = $user === 'demo' && $pwd === 'demo';
        if ($result) {
            $this->id = 1;
        }

        return $result;
    }
}
