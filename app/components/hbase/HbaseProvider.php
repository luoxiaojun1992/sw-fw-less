<?php

namespace App\components\hbase;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

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
