<?php

namespace SwFwLess\components\filewatcher;

class Watcher
{
    private $driverClass;

    /** @var WatcherWrapperContract */
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

    /**
     * @param $events
     * @param $callback
     * @param int $priority
     */
    public function watch($events, $callback, $priority = 0)
    {
        $this->driver->watch($events, $callback, $priority);
    }
}
