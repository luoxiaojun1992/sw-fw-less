<?php

namespace SwFwLess\components\es;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

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
