<?php

namespace SwFwLess\facades\etcd;

use SwFwLess\facades\AbstractFacade;

/**
 * Class Lock
 *
 * @mixin \SwFwLess\components\etcd\Lock
 * @package SwFwLess\facades\etcd
 */
class Lock extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\etcd\Lock::create();
    }
}
