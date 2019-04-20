<?php

namespace SwFwLess\components\filewatcher;

interface WatcherWrapperContract
{
    /**
     * @param $events
     * @param $callback
     * @param int $priority
     */
    public function watch($events, $callback, $priority = 0);
}
