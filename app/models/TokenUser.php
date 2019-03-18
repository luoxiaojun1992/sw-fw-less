<?php

namespace App\models;

use App\components\auth\token\UserProviderContract;
use App\components\RedisWrapper;
use App\facades\RedisPool;

class TokenUser extends AbstractMysqlModel implements UserProviderContract
{
    public function retrieveByToken($authToken)
    {
        /** @var \Redis|RedisWrapper $redis */
        $redis = RedisPool::pick();
        try {
            if ($id = $redis->get('auth:token:' . $authToken)) {
                $this->id = $id;
                return true;
            }
        } catch (\Exception $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }

        return false;
    }
}
