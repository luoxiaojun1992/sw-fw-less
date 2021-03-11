<?php

namespace SwFwLess\components\pool;

use SwFwLess\components\swoole\Scheduler;

abstract class AbstractPool
{
    protected $pool = [];

    protected abstract function createRes($id);

    /**
     * @param $id
     * @return mixed|null
     */
    public function pick($id)
    {
        if (!isset($this->pool[$id])) {
            return null;
        }
        /** @var Poolable $object */
        $object = Scheduler::withoutPreemptive(function () use ($id) {
            return array_pop($this->pool[$id]);
        });
        if (!$object) {
            $object = $this->createRes($id);
            $object->setReleaseToPool(false);
        } else {
            $object->refresh();
            $object->setReleaseToPool(true);
        }
        return $object;
    }

    /**
     * @param Poolable $res
     */
    public function release($res)
    {
        if ($res) {
            if ($res instanceof Poolable) {
                if ($res->needRelease()) {
                    Scheduler::withoutPreemptive(function () use ($res) {
                        $id = $res->getPoolResId();
                        if (isset($this->pool[$id])) {
                            $res->reset();
                            $this->pool[$id][] = $res;
                        }
                    });
                }
            }
        }
    }
}
