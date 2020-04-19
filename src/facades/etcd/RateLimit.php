<?php

namespace SwFwLess\facades\etcd;

use SwFWLess\components\etcd\Client;
use SwFwLess\facades\AbstractFacade;

class RateLimit extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\etcd\RateLimit::create(
            Client::create(config('etcd')),
            config('rate_limit')
        )
    }
}
