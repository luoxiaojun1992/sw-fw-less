<?php

namespace SwFwLess\components\provider;

abstract class AbstractProvider implements ProviderContract
{
    public static function bootCommand()
    {
        //
    }

    public static function bootApp()
    {
        //
    }

    public static function bootWorker()
    {
        //
    }

    public static function bootRequest()
    {
        //
    }

    public static function shutdownApp()
    {
        //
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function shutdownResponse()
    {
        //
    }
}
