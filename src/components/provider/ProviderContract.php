<?php

namespace SwFwLess\components\provider;

interface ProviderContract
{
    public static function bootApp();
    public static function bootWorker();
    public static function bootRequest();
    public static function shutdown();
}
