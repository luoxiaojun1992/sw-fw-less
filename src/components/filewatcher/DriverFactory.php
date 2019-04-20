<?php

namespace SwFwLess\components\filewatcher;

use SwFwLess\components\filewatcher\kwf\WatcherFactory as KwfWatcherFactory;
use SwFwLess\components\filewatcher\sw\WatcherFactory as SwWatcherFactory;

class DriverFactory
{
    const FACTORY_MAPPINGS = [
        \HuangYi\Watcher\Watcher::class => SwWatcherFactory::class,
        \Kwf\FileWatcher\Watcher::class => KwfWatcherFactory::class,
    ];

    /**
     * @param $watcherDriver
     * @param $watchDirs
     * @param array $excludedDirs
     * @param array $suffixes
     * @return WatcherWrapperContract
     * @throws \Exception
     */
    public static function create($watcherDriver, $watchDirs, $excludedDirs = [], $suffixes = [])
    {
        if (isset(static::FACTORY_MAPPINGS[$watcherDriver])) {
            $watcherFactory = static::FACTORY_MAPPINGS[$watcherDriver];
        } else {
            $watcherFactory = KwfWatcherFactory::class;
        }

        return call_user_func_array(
            [$watcherFactory, 'create'],
            [
                'watchDirs' => $watchDirs,
                'excludedDirs' => $excludedDirs,
                'suffixes' => $suffixes,
            ]
        );
    }
}
