<?php

namespace SwFwLess\facades;

use SwFWLess\components\etcd\Client;

class Etcd extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Client::create(config('etcd'));
    }
}
