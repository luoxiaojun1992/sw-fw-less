<?php

namespace SwFwLess\components\filewatcher;

interface WatcherFactoryContract
{
    public static function create($watchDirs, $excludedDirs = [], $suffixes = []);
}
