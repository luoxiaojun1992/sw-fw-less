<?php

namespace App\components\hbase;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

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
