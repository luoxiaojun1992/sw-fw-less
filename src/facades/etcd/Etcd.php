<?php

namespace SwFwLess\facades\etcd;

use SwFWLess\components\etcd\Client;
use SwFwLess\facades\AbstractFacade;

class Etcd extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Client::create(config('etcd'));
    }
}
