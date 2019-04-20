<?php

namespace SwFwLess\components\filewatcher\kwf;

use Kwf\FileWatcher\Backend\BackendAbstract;
use Kwf\FileWatcher\Event\Create;
use Kwf\FileWatcher\Event\Delete;
use Kwf\FileWatcher\Event\Modify;
use Kwf\FileWatcher\Event\Move;
use SwFwLess\components\filewatcher\Watcher as SwfWatcher;
use SwFwLess\components\filewatcher\WatcherWrapperContract;

class Wrapper implements WatcherWrapperContract
{
    const KWF_WATCHER_EVENTS = [
        SwfWatcher::EVENT_MODIFY => Modify::NAME,
        SwfWatcher::EVENT_CREATE => Create::NAME,
        SwfWatcher::EVENT_DELETE => Delete::NAME,
        SwfWatcher::EVENT_DELETE_SELF => Delete::NAME,
        SwfWatcher::EVENT_MOVE => Move::NAME,
        SwfWatcher::EVENT_MOVE_SELF => Move::NAME,
        SwfWatcher::EVENT_MOVED_FROM => Move::NAME,
        SwfWatcher::EVENT_MOVED_TO => Move::NAME,
    ];

    /** @var BackendAbstract */
    private $watcher;

    public static function create($watchDirs)
    {
        return new static($watchDirs);
    }

    public function __construct($watchDirs)
    {
        $this->watcher = KwfWatcher::create($watchDirs);
    }

    /**
     * @param $events
     * @param $callback
     * @param int $priority
     */
    public function watch($events, $callback, $priority = 0)
    {
        $this->watcher->addListener(static::KWF_WATCHER_EVENTS[$events], function ($event) use ($callback) {
            call_user_func_array($callback, [$event]);
        }, $priority)->start();
    }
}
