<?php

namespace SwFwLess\facades\etcd;

use SwFWLess\components\etcd\Client;
use SwFwLess\facades\AbstractFacade;

/**
 * Class Etcd
 * @package SwFwLess\facades\etcd
 */
class Etcd extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Client::create();
    }
}
