<?php

namespace SwFwLess\components\provider;

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

    public static function shutdown()
    {
        parent::shutdown();

        foreach (config('providers') as $provider) {
            call_user_func([$provider, 'shutdown']);
        }
    }
}
