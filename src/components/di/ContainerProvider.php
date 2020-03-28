<?php

namespace SwFwLess\components\di;

use SwFwLess\components\provider\AbstractProvider;

/**
 * Class ContainerProvider
 * @package SwFwLess\components\di
 */
class ContainerProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        Container::create();
    }
}
