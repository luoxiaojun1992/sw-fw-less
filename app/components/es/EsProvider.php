<?php

namespace App\components\es;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

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
