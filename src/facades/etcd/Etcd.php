<?php

namespace SwFwLess\facades\etcd;

use SwFwLess\components\etcd\Client;
use SwFwLess\facades\AbstractFacade;

/**
 * Class Etcd
 *
 * @mixin Client
 * @package SwFwLess\facades\etcd
 */
class Etcd extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Client::create();
    }
}
