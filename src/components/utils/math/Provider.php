<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\provider\AbstractProvider;

class Provider extends AbstractProvider
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        Math::create(\SwFwLess\components\functions\config('util.math'));
    }
}
