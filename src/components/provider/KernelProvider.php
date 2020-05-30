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
        static::$providers = static::mergeProviders(
            $providers,
            self::composerProviders()
        );
    }

    /**
     * 优先执行core providers，保证composer providers能使用核心资源
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

        foreach (static::$providers as $providers) {
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

        foreach (static::$providers as $providers) {
            foreach ($providers as $provider) {
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

        foreach (static::$providers as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'bootRequest']);
            }
        }
    }

    public static function shutdownApp()
    {
        parent::shutdownApp();

        foreach (static::$providers as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'shutdownApp']);
            }
        }
    }

    public static function shutdownWorker()
    {
        parent::shutdownWorker();

        foreach (static::$providers as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'shutdownWorker']);
            }
        }
    }

    public static function shutdownResponse()
    {
        parent::shutdownResponse();

        foreach (static::$providers as $providers) {
            foreach ($providers as $provider) {
                call_user_func([$provider, 'shutdownResponse']);
            }
        }
    }
}
