<?php

namespace SwFwLess\facades\etcd;

use SwFwLess\facades\AbstractFacade;

/**
 * Class RateLimit
 *
 * @mixin \SwFwLess\components\etcd\RateLimit
 * @package SwFwLess\facades\etcd
 */
class RateLimit extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\etcd\RateLimit::create()
    }
}
