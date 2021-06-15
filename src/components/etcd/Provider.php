<?php

namespace SwFwLess\components\etcd;

use SwFwLess\components\provider\RequestProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\ratelimit\RateLimitFactory;

class Provider implements WorkerProviderContract, RequestProviderContract
{
    public static function bootWorker()
    {
        Client::create(\SwFwLess\components\functions\config('etcd'));
        RateLimit::create(
            Client::create(),
            \SwFwLess\components\functions\config(
                'rate_limit.drivers.' . RateLimitFactory::ALGORITHM_ETCD_LEAKY_BUCKET
            )
        );
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function bootRequest()
    {
        Lock::create(Client::create(), \SwFwLess\components\functions\config('lock', []));
    }

    public static function shutdownResponse()
    {
        //
    }
}
