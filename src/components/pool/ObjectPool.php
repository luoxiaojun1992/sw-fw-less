<?php

namespace SwFwLess\components\pool;

use SwFwLess\bootstrap\App;
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
        return (self::$instance instanceof self) ?
            (self::$instance) :
            (
            (is_array($config) && (!empty($config['switch']))) ?
                (self::$instance = new self($config)) :
                null
            );
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
        return (\SwFwLess\components\di\Container::diSwitch()) ?
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
        $releaseToPool = $object ? true : false;
        //inline optimization, see SwFwLess\components\di\Container::diSwitch()
        //inline optimization, see static::createObject()
        $object = $object ?: ($this->pool[$class]) ?? (
            (\SwFwLess\components\Config::get(
                'di_switch', \SwFwLess\components\di\Container::DEFAULT_DI_SWITCH)) ?
                Container::make($class) :
                new $class);
        $object->setReleaseToPool($releaseToPool);
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
