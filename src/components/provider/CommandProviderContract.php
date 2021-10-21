<?php

namespace SwFwLess\components\provider;

interface CommandProviderContract
{
    public static function bootCommand();
    public static function shutdownCommand();
}
