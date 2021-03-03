<?php

namespace SwFwLess\components\pool;

interface Poolable
{
    public function reset();

    public function needRelease();

    public function setReleaseToPool(bool $releaseToPool);

    public function getPoolResId();
}
