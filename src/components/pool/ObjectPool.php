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
        $this->config = array_merge($this->config, $config);

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
        $object = Scheduler::withoutPreemptive(function () use ($class) {
            return isset($this->pool[$class]) ? array_pop($this->pool[$class]) : null;
        });
        $object = $object ?: ($this->pool[$class]) ?? $this->createObject($class);
        $object ? $object->setReleaseToPool(false) : null;
        return $object;
    }

    /**
     * @param Poolable $object
     */
    public function release($object)
    {
        if ($object && ($object instanceof Poolable) && ($object->needRelease())) {
            Scheduler::withoutPreemptive(function () use ($object) {
                $class = get_class($object);
                if (isset($this->pool[$class])) {
                    $object->reset();
                    $this->pool[$class][] = $object;
                }
            });
        }
    }

    /**
     * @return array
     */
    public function stats()
    {
        $total = 0;
        $classPoolCounter = [];
        foreach ($this->pool as $className => $objects) {
            $total += $classPoolCounter[$className] = count($objects);
        }
        return [
            'classes' => $classPoolCounter,
            'total' => $total,
        ];
    }
}
