<?php

namespace SwFwLess\components\auth;

abstract class AbstractGuard implements GuardContract
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }
}
