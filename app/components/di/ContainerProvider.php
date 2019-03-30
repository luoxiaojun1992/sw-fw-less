<?php

namespace App\components\di;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

/**
 * Class ContainerProvider
 * @package App\components\di
 */
class ContainerProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Container::create();
    }
}
