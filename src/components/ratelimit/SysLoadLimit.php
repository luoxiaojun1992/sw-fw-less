<?php

namespace SwFwLess\components\ratelimit;

class SysLoadLimit
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
     * SysLoadLimit constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function pass($metric, $period, $throttle, &$remaining = null)
    {
        $sysLoadArr = sys_getloadavg();

        if ($period <= 60) {
            $sysLoad = $sysLoadArr[0];
        } elseif ($period <= 300) {
            $sysLoad = $sysLoadArr[1];
        } else {
            $sysLoad = $sysLoadArr[2];
        }

        if ($sysLoad >= $throttle) {
            return false;
        }

        $remaining = $throttle - $sysLoad;
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
