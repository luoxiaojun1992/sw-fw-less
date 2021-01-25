<?php

namespace SwFwLess\components\provider;

class KernelProvider extends AbstractProvider
{
    private static $providers = [];

    private static $appProviders = [];

    private static $workerProviders = [];

    private static $requestProviders = [];

    /**
     * @param $providers
     * @throws \ReflectionException
     */
    public static function init($providers)
    {
        static::$providers = static::mergeProviders(
            $providers,
            self::composerProviders()
        );

        static::classify();
    }

    /**
     * @throws \ReflectionException
     */
    protected static function classify()
    {
        foreach (static::$providers as $providers) {
            $appProviders = [];
            $workerProviders = [];
            $requestProviders = [];

            foreach ($providers as $provider) {
                $providerReflection = new \ReflectionClass($provider);
                if ($providerReflection->implementsInterface(ProviderContract::class)) {
                    $appProviders[] = $provider;
                    $workerProviders[] = $provider;
                    $requestProviders[] = $provider;
                } else{
                    if ($providerReflection->implementsInterface(AppProviderContract::class)) {
                        $appProviders[] = $provider;
                    }
                    if ($providerReflection->implementsInterface(WorkerProviderContract::class)) {
                        $workerProviders[] = $provider;
                    }
                    if ($providerReflection->implementsInterface(RequestProviderContract::class)) {
                        $requestProviders[] = $provider;
                    }
                }
            }

            static::$appProviders[] = $appProviders;
            static::$workerProviders[] = $workerProviders;
            static::$requestProviders[] = $requestProviders;
        }
    }

    /**
     * Give priority to core providers，to ensure core resources have been loaded before
     * booting composer providers.
     *
     * @param $configProviders
     * @param $composerProviders
     * @return array
     */
    private static function mergeProviders($configProviders, $composerProviders)
    {
        return [$configProviders, $composerProviders];
    }

    protected static function excludedComposerProviders()
    {
        $composerJsonPath = APP_BASE_PATH . 'composer.json';
        if (file_exists($composerJsonPath)) {
            $composerJson = file_get_contents($composerJsonPath);
            if ($composerJson) {
                $composerConfig = json_decode($composerJson, true);
                if (isset($composerConfig['extra']['sw-fw-less']['excluded'])) {
                    return $composerConfig['extra']['sw-fw-less']['excluded'];
                }
            }
        }

        return [];
    }

    /**
     * @return mixed
     */
    private static function composerProviders()
    {
        $providers = [];

        $composerInstalled = file_get_contents(APP_BASE_PATH . 'vendor/composer/installed.json');
        if ($composerInstalled) {
            $packages = json_decode($composerInstalled, true);
            if (isset($packages['packages'])) {
                $packages = $packages['packages'];
            }
            $excludedProviders = static::excludedComposerProviders();
            foreach ($packages as $package) {
                if (isset($package['extra']['sw-fw-less']['providers'])) {
                    $composerProviders = array_diff(
                        $package['extra']['sw-fw-less']['providers'],
                        $excludedProviders
                    );
                    $providers = array_merge($providers, $composerProviders);
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

        foreach (static::$appProviders as $providers) {
            foreach ($providers as $provider) {
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

        foreach (static::$workerProviders as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'bootWorker']);
            }
        }
    }

    public static function bootRequest()
    {
        parent::bootRequest();

        foreach (static::$requestProviders as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'bootRequest']);
            }
        }
    }

    public static function shutdownApp()
    {
        parent::shutdownApp();

        foreach (static::$appProviders as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'shutdownApp']);
            }
        }
    }

    public static function shutdownWorker()
    {
        parent::shutdownWorker();

        foreach (static::$workerProviders as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'shutdownWorker']);
            }
        }
    }

    public static function shutdownResponse()
    {
        parent::shutdownResponse();

        foreach (static::$requestProviders as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'shutdownResponse']);
            }
        }
    }
}
