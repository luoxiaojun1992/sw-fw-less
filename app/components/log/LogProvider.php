<?php

namespace App\components\log;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class LogProvider extends AbstractProvider implements RequestProvider
{
    /**
     * @throws \Exception
     */
    public static function bootRequest()
    {
        parent::bootRequest();
        if (config('log.switch')) {
            Log::create(
                config('log.path'),
                config('log.level'),
                config('log.pool_size'),
                config('log.buffer_max_size'),
                config('log.name'),
                config('log.reserve_days')
            );
        }
    }
}
