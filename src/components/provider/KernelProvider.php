<?php

namespace SwFwLess\components\provider;

class KernelProvider extends AbstractProvider
{
    private static $providers = [];

    private static $commandProviders = [];

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
        foreach (static::$providers as $provider) {
            $providerReflection = new \ReflectionClass($provider);
            if ($providerReflection->implementsInterface(ProviderContract::class)) {
                static::$commandProviders[] = $provider;
                static::$appProviders[] = $provider;
                static::$workerProviders[] = $provider;
                static::$requestProviders[] = $provider;
            } else {
                if ($providerReflection->implementsInterface(CommandProviderContract::class)) {
                    static::$commandProviders[] = $provider;
                }
                if ($providerReflection->implementsInterface(AppProviderContract::class)) {
                    static::$appProviders[] = $provider;
                }
                if ($providerReflection->implementsInterface(WorkerProviderContract::class)) {
                    static::$workerProviders[] = $provider;
                }
                if ($providerReflection->implementsInterface(RequestProviderContract::class)) {
                    static::$requestProviders[] = $provider;
                }
            }
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
        return array_merge($configProviders, $composerProviders);
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

    public static function bootCommand()
    {
        parent::bootCommand();

        foreach (static::$commandProviders as $provider) {
            call_user_func([$provider, 'bootCommand']);
        }
    }

    public static function bootApp()
    {
        parent::bootApp();

        foreach (static::$appProviders as $provider) {
            call_user_func([$provider, 'bootApp']);
        }
    }

    public static function bootWorker()
    {
        parent::bootWorker();

        foreach (static::$workerProviders as $provider) {
            call_user_func([$provider, 'bootWorker']);
        }
    }

    public static function bootRequest()
    {
        parent::bootRequest();

        foreach (static::$requestProviders as $provider) {
            call_user_func([$provider, 'bootRequest']);
        }
    }

    public static function shutdownApp()
    {
        parent::shutdownApp();

        foreach (static::$appProviders as $provider) {
            call_user_func([$provider, 'shutdownApp']);
        }
    }

    public static function shutdownWorker()
    {
        parent::shutdownWorker();

        foreach (static::$workerProviders as $provider) {
            call_user_func([$provider, 'shutdownWorker']);
        }
    }

    public static function shutdownResponse()
    {
        parent::shutdownResponse();

        foreach (static::$requestProviders as $provider) {
            call_user_func([$provider, 'shutdownResponse']);
        }
    }
}
