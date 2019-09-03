<?php

namespace SwFwLess\components\di;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

/**
 * Class ContainerProvider
 * @package SwFwLess\components\di
 */
class ContainerProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Container::create();
    }
}
