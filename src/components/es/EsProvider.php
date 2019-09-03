<?php

namespace SwFwLess\components\es;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class EsProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (config('elasticsearch.switch')) {
            Manager::create();
        }
    }
}
