<?php

namespace SwFwLess\facades\etcd;

use SwFwLess\facades\AbstractFacade;

class RateLimit extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\etcd\RateLimit::create()
    }
}
