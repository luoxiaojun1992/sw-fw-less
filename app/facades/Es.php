<?php

namespace App\facades;

use App\components\es\Manager;
use Elasticsearch\Client;

/**
 * Class Es
 *
 * @method static Client|null connection($connection_name = 'default')
 * @package App\facades
 */
class Es extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Manager::create();
    }
}
