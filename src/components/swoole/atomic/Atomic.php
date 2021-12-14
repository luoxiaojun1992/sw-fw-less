<?php

namespace SwFwLess\components\swoole\atomic;

class Atomic
{
    /** @var \Swoole\Atomic[] */
    public static $atomicPool = [];

    public static function init($atomicConfig = [])
    {
        $atomicPoolConfig = $atomicConfig['pool'] ?? [];
        foreach ($atomicPoolConfig as $atomicObjConfig) {
            static::$atomicPool[$atomicObjConfig['name']] = new \Swoole\Atomic(
                $atomicObjConfig['init_val'] ?? 0
            );
        }
    }

    public static function create($id, $initVal = 0)
    {
        return static::$atomicPool[$id] = new \Swoole\Atomic($initVal);
    }

    public static function put($id, $atomic)
    {
        static::$atomicPool[$id] = $atomic;
    }

    public static function reload()
    {
        foreach (static::$atomicPool as $atomic) {
            $atomic->set(0);
        }
    }

    /**
     * @param $id
     * @return \Swoole\Atomic|null
     */
    public static function pick($id)
    {
        return (static::$atomicPool[$id]) ?? null;
    }
}
