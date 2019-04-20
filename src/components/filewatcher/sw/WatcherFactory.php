<?php

namespace SwFwLess\components\filewatcher\sw;

use SwFwLess\components\filewatcher\WatcherFactoryContract;

class WatcherFactory implements WatcherFactoryContract
{
    public static function create($watchDirs, $excludedDirs = [], $suffixes = [])
    {
        return Wrapper::create($watchDirs, $excludedDirs, $suffixes);
    }
}
