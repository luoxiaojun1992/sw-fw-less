<?php

namespace App\components\core;

interface ProviderContract
{
    public static function bootApp();
    public static function bootRequest();
}
