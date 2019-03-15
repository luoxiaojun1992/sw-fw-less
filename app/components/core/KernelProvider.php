<?php

namespace App\components\core;

class KernelProvider extends AbstractProvider
{
    /**
     * @throws \ReflectionException
     */
    public static function bootApp()
    {
        parent::bootApp();

        foreach (config('providers') as $provider) {
            if ((new \ReflectionClass($provider))->implementsInterface(AppProvider::class)) {
                call_user_func([$provider, 'bootApp']);
            }
        }
    }

    /**
     * @throws \ReflectionException
     */
    public static function bootRequest()
    {
        parent::bootRequest();

        foreach (config('providers') as $provider) {
            if ((new \ReflectionClass($provider))->implementsInterface(RequestProvider::class)) {
                call_user_func([$provider, 'bootRequest']);
            }
        }
    }
}
