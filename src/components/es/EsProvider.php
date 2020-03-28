<?php

namespace SwFwLess\components\es;

use SwFwLess\components\provider\AbstractProvider;

class EsProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (config('elasticsearch.switch')) {
            Manager::create();
        }
    }
}
