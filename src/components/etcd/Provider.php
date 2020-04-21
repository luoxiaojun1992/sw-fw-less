<?php

namespace SwFwLess\components\etcd;

use SwFwLess\components\provider\AbstractProvider;

class Provider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Client::create(config('etcd'));
        RateLimit::create(Client::create(), config('rate_limit'));
    }

    public static function bootRequest()
    {
        parent::bootRequest();

        Lock::create(Client::create(), config('lock', []));
    }
}
