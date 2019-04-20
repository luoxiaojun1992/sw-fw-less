<?php

namespace SwFwLess\components\filewatcher;

use Kwf\FileWatcher\Backend\BackendAbstract;
use Kwf\FileWatcher\Event\Create;
use Kwf\FileWatcher\Event\Delete;
use Kwf\FileWatcher\Event\Modify;
use Kwf\FileWatcher\Event\Move;

class Watcher
{
    private $driverClass;

    private $driver;

    private $watchDirs;

    private $excludedDirs;

    private $suffixes;

    const EVENT_MODIFY = 0;
    const EVENT_CREATE = 1;
    const EVENT_DELETE = 2;
    const EVENT_DELETE_SELF = 3;
    const EVENT_MOVE = 4;
    const EVENT_MOVE_SELF = 5;
    const EVENT_MOVED_FROM = 6;
    const EVENT_MOVED_TO = 7;

    const KWF_WATCHER_EVENTS = [
        self::EVENT_MODIFY => Modify::NAME,
        self::EVENT_CREATE => Create::NAME,
        self::EVENT_DELETE => Delete::NAME,
        self::EVENT_DELETE_SELF => Delete::NAME,
        self::EVENT_MOVE => Move::NAME,
        self::EVENT_MOVE_SELF => Move::NAME,
        self::EVENT_MOVED_FROM => Move::NAME,
        self::EVENT_MOVED_TO => Move::NAME,
    ];

    /**
     * Watcher constructor.
     * @param $driver
     * @param $watchDirs
     * @param array $excludedDirs
     * @param array $suffixes
     * @throws \Exception
     */
    public function __construct($driver, $watchDirs, $excludedDirs = [], $suffixes = [])
    {
        $this->driverClass = $driver;
        $this->watchDirs = $watchDirs;
        $this->excludedDirs = $excludedDirs;
        $this->suffixes = $suffixes;
        $this->createDriver();
    }

    /**
     * @param $driver
     * @param $watchDirs
     * @param array $excludedDirs
     * @param array $suffixes
     * @return Watcher
     * @throws \Exception
     */
    public static function create($driver, $watchDirs, $excludedDirs = [], $suffixes = [])
    {
        return new static($driver, $watchDirs, $excludedDirs, $suffixes);
    }

    /**
     * @throws \Exception
     */
    private function createDriver()
    {
        $this->driver = DriverFactory::create(
            $this->driverClass,
            $this->watchDirs,
            $this->excludedDirs,
            $this->suffixes
        );
    }

    private function swWatcherEvents($events)
    {
        $swEvents = [];
        foreach ($events as $event) {
            switch ($event) {
                case static::EVENT_MODIFY:
                    $swEvents[] = IN_MODIFY;
                    break;
                case static::EVENT_CREATE:
                    $swEvents[] = IN_CREATE;
                    break;
                case static::EVENT_DELETE:
                    $swEvents[] = IN_DELETE;
                    break;
                case static::EVENT_DELETE_SELF:
                    $swEvents[] = IN_DELETE_SELF;
                    break;
                case static::EVENT_MOVE:
                    $swEvents[] = IN_MOVE;
                    break;
                case static::EVENT_MOVE_SELF:
                    $swEvents[] = IN_MOVE_SELF;
                    break;
                case static::EVENT_MOVED_FROM:
                    $swEvents[] = IN_MOVED_FROM;
                    break;
                case static::EVENT_MOVED_TO:
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
        if ($this->driver instanceof \HuangYi\Watcher\Watcher) {
            if (!is_array($events)) {
                $events = [$events];
            }
            $this->driver->setHandler(function ($watcher, $event) use ($callback) {
                call_user_func_array($callback, [$event]);
            })->setMasks($this->swWatcherEvents($events))->watch();
        } elseif ($this->driver instanceof BackendAbstract) {
            $this->driver->addListener(static::KWF_WATCHER_EVENTS[$events], function ($event) use ($callback) {
                call_user_func_array($callback, [$event]);
            }, $priority)->start();
        }
    }
}
