<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\redis\RedisPool;

class SlidingWindow
{
    //TODO

    private static $instance;

    /**
     * @var RedisPool
     */
    private $redisPool;

    private $config = ['connection' => 'sliding_window'];
}
