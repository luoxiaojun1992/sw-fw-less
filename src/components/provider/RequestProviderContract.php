<?php

namespace SwFwLess\components\provider;

interface RequestProviderContract
{
    public static function bootRequest();
    public static function shutdownResponse();
}
