<?php

namespace App\components\provider;

interface ProviderContract
{
    public static function bootApp();
    public static function bootRequest();
    public static function shutdown();
}