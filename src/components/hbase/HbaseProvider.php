<?php

namespace SwFwLess\components\hbase;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class HbaseProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('hbase.switch')) {
            HbasePool::create();
        }
    }
}
