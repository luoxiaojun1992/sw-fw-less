<?php

namespace SwFwLess\components\filewatcher\kwf;

use SwFwLess\components\filewatcher\WatcherFactoryContract;

class WatcherFactory implements WatcherFactoryContract
{
    public static function create($watchDirs, $excludedDirs = [], $suffixes = [])
    {
        return Wrapper::create($watchDirs);
    }
}
