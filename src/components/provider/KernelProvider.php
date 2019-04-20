<?php

namespace SwFwLess\components\provider;

use SwFwLess\facades\File;

class KernelProvider extends AbstractProvider
{
    private static $providers = [];

    /**
     * @param $providers
     */
    public static function init($providers)
    {
        static::$providers = self::mergeComposerProviders($providers);
    }

    private static function mergeComposerProviders($providers)
    {
        $composerInstalled = File::prepare()->read(File::path('vendor/composer/installed.json'));
        if ($composerInstalled) {
            $packages = json_decode($composerInstalled, true);
            foreach ($packages as $package) {
                if (isset($package['extra']['sw-fw-less']['provider'])) {
                    array_push($providers, $package['extra']['sw-fw-less']['provider']);
                }
            }
        }

        return $providers;
    }

    /**
     * @throws \ReflectionException
     */
    public static function bootApp()
    {
        parent::bootApp();

        foreach (static::$providers as $provider) {
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

        foreach (static::$providers as $provider) {
            if ((new \ReflectionClass($provider))->implementsInterface(RequestProvider::class)) {
                call_user_func([$provider, 'bootRequest']);
            }
        }
    }

    public static function shutdown()
    {
        parent::shutdown();

        foreach (static::$providers as $provider) {
            call_user_func([$provider, 'shutdown']);
        }
    }
}
