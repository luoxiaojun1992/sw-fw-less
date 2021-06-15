<?php

namespace SwFwLess\components\ratelimit;

class MemLimit
{
    private static $instance;

    private $config = [];

    public static function clearInstance()
    {
        static::$instance = null;
    }

    /**
     * @param array|null $config
     * @return static
     */
    public static function create($config = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (!is_null($config)) {
            return self::$instance = new self($config);
        }

        return null;
    }

    /**
     * MemLimit constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function pass($metric, $period, $throttle, &$remaining = null)
    {
        $memoryUsage = memory_get_usage();
        if ($memoryUsage >= $throttle) {
            return false;
        }

        $remaining = $throttle - $memoryUsage;
        return true;
    }

    public function supportGivingBack()
    {
        return false;
    }

    public function clear($metric)
    {
        return false;
    }
}
