<?php

namespace SwFwLess\components\log;

use SwFwLess\components\Config;
use Lxj\Monolog\Co\Stream\Handler;
use Monolog\Logger;
use SwFwLess\components\swoole\Scheduler;

class Log
{
    private static $instance;

    /** @var Logger $logger */
    private $logger;

    private $loggerDate;

    private $logPath;

    private $level = Logger::DEBUG;

    private $syncLevels = [];

    private $poolSize = 100;

    private $bufferMaxSize = 10;

    private $name = 'sw-fw-less';

    private $reserveDays = 3;

    private $rotateLock = [true];

    /**
     * @param string $log_path
     * @param int $level
     * @param array $syncLevels
     * @param int $pool_size
     * @param int $buffer_max_size
     * @param string $name
     * @param int $reserve_days
     * @return Log
     * @throws \Exception
     */
    public static function create(
        $log_path = '',
        $level = Logger::DEBUG,
        $syncLevels = [],
        $pool_size = 100,
        $buffer_max_size = 10,
        $name = 'sw-fw-less',
        $reserve_days = 3
    )
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('log.switch')) {
            return self::$instance = new self(
                $log_path, $level, $syncLevels, $pool_size, $buffer_max_size, $name, $reserve_days
            );
        } else {
            return null;
        }
    }

    /**
     * Log constructor.
     * @param $log_path
     * @param int $level
     * @param array $syncLevels
     * @param int $pool_size
     * @param int $buffer_max_size
     * @param string $name
     * @param int $reserve_days
     * @throws \Exception
     */
    public function __construct(
        $log_path,
        $level = Logger::DEBUG,
        $syncLevels = [],
        $pool_size = 100,
        $buffer_max_size = 10,
        $name = 'sw-fw-less',
        $reserve_days = 3
    )
    {
        $this->logPath = $log_path;
        $this->level = $level;
        $this->syncLevels = $syncLevels;
        $this->poolSize = $pool_size;
        $this->bufferMaxSize = $buffer_max_size;
        $this->name = $name;
        $this->reserveDays = $reserve_days;

        $this->logger = $this->createLogger();
    }

    /**
     * @return Logger
     * @throws \Exception
     */
    private function createLogger()
    {
        $this->loggerDate = date('Ymd');
        $logPath = $this->getLogPath($this->loggerDate);
        $handler = new Handler(
            $logPath,
            $this->level,
            $this->syncLevels,
            true,
            null,
            $this->poolSize,
            $this->bufferMaxSize
        );
        $logger = new Logger($this->name);
        $logger->pushHandler($handler);
        $this->removeLogs();
        return $logger;
    }

    /**
     * @param $loggerDate
     * @return mixed
     */
    private function getLogPath($loggerDate)
    {
        return str_replace('{date}', $loggerDate, $this->logPath);
    }

    private function rotateLock()
    {
        return Scheduler::withoutPreemptive(function () {
            return array_pop($this->rotateLock);
        });
    }

    /**
     * @throws \Throwable
     */
    private function rotate()
    {
        if (date('Ymd') != $this->loggerDate) {
            if ($this->rotateLock()) {
                try {
                    if (date('Ymd') != $this->loggerDate) {
                        $this->logger = $this->createLogger();
                    }
                } catch (\Throwable $e) {
                    throw $e;
                } finally {
                    array_push($this->rotateLock, true);
                }
            }
        }
    }

    private function removeLogs()
    {
        for ($i = 0; $i < $this->reserveDays; ++$i) {
            $loggerDate = date('Ymd', strtotime('-' . (string)($this->reserveDays + $i) . ' days'));
            @unlink($this->getLogPath($loggerDate));
        }
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     * @throws \Throwable
     */
    public function __call($name, $arguments)
    {
        $this->rotate();

        $logger = $this->logger;

        if (method_exists($logger, $name)) {
            return call_user_func_array([$logger, $name], $arguments);
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function countRecordBuffer()
    {
        $count = 0;

        array_map(function ($handler) use (&$count) {
            if ($handler instanceof Handler) {
                $count += $handler->countRecordBuffer();
            }
        }, $this->logger->getHandlers());

        return $count;
    }

    /**
     * @return int|null
     */
    public function countPool()
    {
        $count = 0;

        array_map(function ($handler) use (&$count) {
            if ($handler instanceof Handler) {
                $count += $handler->getStreamPool()->countPool();
            }
        }, $this->logger->getHandlers());

        return $count;
    }

    public function flush()
    {
        array_map(function ($handler) {
            if ($handler instanceof Handler) {
                $handler->flush();
            }
        }, $this->logger->getHandlers());
    }
}
