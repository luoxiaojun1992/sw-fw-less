<?php

namespace SwFwLess\components\provider;

interface AppProviderContract
{
    public static function bootApp();
    public static function shutdownApp();
}
