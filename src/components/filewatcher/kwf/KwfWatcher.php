<?php

namespace SwFwLess\components\filewatcher\kwf;

use Kwf\FileWatcher\Backend as Backend;
use Kwf\FileWatcher\Watcher;

class KwfWatcher extends Watcher
{
    /**
     * Creates instance of best watcher backend for your system.
     */
    public static function create($paths)
    {
        $backends = array(
            new Backend\Inotifywait($paths),
            new Backend\Fswatch($paths),
            new Backend\Watchmedo($paths),
            new Backend\Inotify($paths),
            new Poll($paths),
        );
        foreach ($backends as $b) {
            if ($b->isAvailable()) {
                $backend = $b;
                break;
            }
        }
        return $backend;
    }
}
