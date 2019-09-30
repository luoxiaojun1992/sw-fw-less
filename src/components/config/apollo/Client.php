<?php

namespace SwFwLess\components\config\apollo;

class Client
{
    use ConfigSetter;

    public static function create()
    {
        return new static();
    }

    public function __construct()
    {
        //
    }

    public function pullConfig()
    {
        //todo implements client
        return [];
    }
}
