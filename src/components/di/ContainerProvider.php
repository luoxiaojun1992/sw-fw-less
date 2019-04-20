<?php

namespace SwFwLess\components\di;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

/**
 * Class ContainerProvider
 * @package SwFwLess\components\di
 */
class ContainerProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Container::create();
    }
}
