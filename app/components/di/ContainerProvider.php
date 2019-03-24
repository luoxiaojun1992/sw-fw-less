<?php

namespace App\components\di;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

/**
 * Class ContainerProvider
 * @package App\components\di
 */
class ContainerProvider extends AbstractProvider implements RequestProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        Container::create();
    }
}
