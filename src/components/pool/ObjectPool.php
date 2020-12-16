<?php

namespace SwFwLess\components\pool;

use SwFwLess\components\swoole\Scheduler;
use SwFwLess\facades\Container;

class ObjectPool
{
    private $pool = [];

    private static $instance;

    private $config = [];

    /**
     * @param null $config
     * @return ObjectPool|null
     */
    public static function create($config = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (is_array($config) && !empty($config['switch'])) {
            return self::$instance = new self($config);
        } else {
            return null;
        }
    }

    /**
     * ObjectPool constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        foreach ($config['objects'] as $class => $options) {
            for ($i = 0; $i < $options['pool_size']; ++$i) {
                $this->pool[$class][] = $this->createObject($class);
            }
        }
    }

    /**
     * @param $class
     * @return Poolable
     */
    public function createObject($class)
    {
        $routeDiSwitch = \SwFwLess\components\di\Container::diSwitch();
        return $routeDiSwitch ?
            Container::make($class) :
            new $class;
    }

    /**
     * @param $class
     * @return mixed|null
     */
    public function pick($class)
    {
        if (!isset($this->pool[$class])) {
            return null;
        }
        $object = Scheduler::withoutPreemptive(function () use ($class) {
            return array_pop($this->pool[$class]);
        });
        if (!$object) {
            $object = $this->createObject($class);
            $object->setReleaseToPool(false);
        } else {
            $object->setReleaseToPool(true);
        }
        return $object;
    }

    /**
     * @param Poolable $object
     */
    public function release($object)
    {
        if ($object) {
            if ($object instanceof Poolable) {
                if ($object->needRelease()) {
                    Scheduler::withoutPreemptive(function () use ($object) {
                        $class = get_class($object);
                        if (isset($this->pool[$class])) {
                            $object->reset();
                            $this->pool[$class][] = $object;
                        }
                    });
                }
            }
        }
    }

    /**
     * @return array
     */
    public function stats()
    {
        $stats = [];
        foreach ($this->pool as $className => $objects) {
            $stats[$className] = count($objects);
        }
        return $stats;
    }
}
