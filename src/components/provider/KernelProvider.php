<?php

namespace SwFwLess\components\provider;

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
        $composerInstalled = file_get_contents(APP_BASE_PATH . 'vendor/composer/installed.json');
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
    public static function bootWorker()
    {
        parent::bootWorker();

        foreach (static::$providers as $provider) {
            if ((new \ReflectionClass($provider))->implementsInterface(WorkerProvider::class)) {
                call_user_func([$provider, 'bootWorker']);
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

    public static function shutdownResponse()
    {
        parent::shutdownResponse();

        foreach (static::$providers as $provider) {
            call_user_func([$provider, 'shutdownResponse']);
        }
    }
}
