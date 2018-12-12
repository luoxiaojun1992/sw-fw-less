<?php

namespace App\components;

use Monolog\Logger;

class Log
{
    private static $instance;

    /** @var \Monolog\Logger $logger */
    private $logger;

    /**
     * @param $log_path
     * @param int $level
     * @param int $pool_size
     * @param int $buffer_max_size
     * @return Log
     * @throws \Exception
     */
    public static function create($log_path = '', $level = Logger::DEBUG, $pool_size = 100, $buffer_max_size = 10)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($log_path, $level, $pool_size, $buffer_max_size);
    }

    /**
     * Log constructor.
     * @param $log_path
     * @param int $level
     * @param int $pool_size
     * @param int $buffer_max_size
     * @param string $name
     * @throws \Exception
     */
    public function __construct($log_path, $level = Logger::DEBUG, $pool_size = 100, $buffer_max_size = 10, $name = 'sw-fw-less')
    {
        $handler = new \Lxj\Monolog\Co\Stream\Handler(
            $log_path,
            $level,
            true,
            null,
            $pool_size,
            $buffer_max_size
        );
        $this->logger = new Logger($name);
        $this->logger->pushHandler($handler);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->logger, $name)) {
            return call_user_func_array([$this->logger, $name], $arguments);
        }

        return null;
    }
}
