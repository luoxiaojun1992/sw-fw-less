<?php

namespace App\components\es;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class EsProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('elasticsearch.switch')) {
            Manager::create();
        }
    }
}
