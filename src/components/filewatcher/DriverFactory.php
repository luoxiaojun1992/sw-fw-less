<?php

namespace SwFwLess\components\filewatcher;

use Kwf\FileWatcher\Backend\BackendAbstract;

class DriverFactory
{
    /**
     * @param $watcherDriver
     * @param $watchDirs
     * @param array $excludedDirs
     * @param array $suffixes
     * @return \HuangYi\Watcher\Watcher|BackendAbstract
     * @throws \Exception
     */
    public static function create($watcherDriver, $watchDirs, $excludedDirs = [], $suffixes = [])
    {
        switch ($watcherDriver) {
            case \HuangYi\Watcher\Watcher::class:
                return new \HuangYi\Watcher\Watcher($watchDirs, $excludedDirs, $suffixes);
            case \Kwf\FileWatcher\Watcher::class:
            default:
                return \Kwf\FileWatcher\Watcher::create($watchDirs);
        }
    }
}
