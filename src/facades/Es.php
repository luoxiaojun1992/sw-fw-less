<?php

namespace SwFwLess\facades;

use SwFwLess\components\es\Manager;
use Elasticsearch\Client;

/**
 * Class Es
 *
 * @method static Client|null connection($connection_name = 'default')
 * @package SwFwLess\facades
 */
class Es extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Manager::create();
    }
}
