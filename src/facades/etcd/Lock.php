<?php

namespace SwFwLess\facades\etcd;

use SwFwLess\facades\AbstractFacade;

class Lock extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\etcd\Lock::create();
    }
}
