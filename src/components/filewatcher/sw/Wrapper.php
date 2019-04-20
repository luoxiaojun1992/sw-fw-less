<?php

namespace SwFwLess\components\filewatcher\sw;

use HuangYi\Watcher\Watcher as SwWatcher;
use SwFwLess\components\filewatcher\Watcher as SwfWatcher;
use SwFwLess\components\filewatcher\WatcherWrapperContract;

class Wrapper implements WatcherWrapperContract
{
    /** @var \HuangYi\Watcher\Watcher */
    private $watcher;

    public static function create($watchDirs, $excludedDirs = [], $suffixes = [])
    {
        return new static($watchDirs, $excludedDirs, $suffixes);
    }

    public function __construct($watchDirs, $excludedDirs = [], $suffixes = [])
    {
        $this->watcher = new SwWatcher($watchDirs, $excludedDirs, $suffixes);
    }

    private function swWatcherEvents($events)
    {
        $swEvents = [];
        foreach ($events as $event) {
            switch ($event) {
                case SwfWatcher::EVENT_MODIFY:
                    $swEvents[] = IN_MODIFY;
                    break;
                case SwfWatcher::EVENT_CREATE:
                    $swEvents[] = IN_CREATE;
                    break;
                case SwfWatcher::EVENT_DELETE:
                    $swEvents[] = IN_DELETE;
                    break;
                case SwfWatcher::EVENT_DELETE_SELF:
                    $swEvents[] = IN_DELETE_SELF;
                    break;
                case SwfWatcher::EVENT_MOVE:
                    $swEvents[] = IN_MOVE;
                    break;
                case SwfWatcher::EVENT_MOVE_SELF:
                    $swEvents[] = IN_MOVE_SELF;
                    break;
                case SwfWatcher::EVENT_MOVED_FROM:
                    $swEvents[] = IN_MOVED_FROM;
                    break;
                case SwfWatcher::EVENT_MOVED_TO:
                    $swEvents[] = IN_MOVED_TO;
                    break;
            }
        }

        return $swEvents;
    }

    /**
     * @param $events
     * @param $callback
     * @param int $priority
     */
    public function watch($events, $callback, $priority = 0)
    {
        if (!is_array($events)) {
            $events = [$events];
        }
        $this->watcher->setHandler(function ($watcher, $event) use ($callback) {
            call_user_func_array($callback, [$event]);
        })->setMasks($this->swWatcherEvents($events))->watch();
    }
}
