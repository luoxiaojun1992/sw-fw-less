<?php

namespace SwFwLess\components\swoole;

use SwFwLess\facades\Container;

class Server
{
    const DI_RESOURCE_ID = 'swoole.server';

    /**
     * @return \Swoole\Http\Server
     */
    public static function getInstance()
    {
        return Container::get(self::DI_RESOURCE_ID);
    }

    /**
     * @param \Swoole\Http\Server $server
     */
    public static function setInstance($server)
    {
        Container::set(self::DI_RESOURCE_ID, $server);
    }
}
