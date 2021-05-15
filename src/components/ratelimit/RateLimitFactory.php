<?php

namespace SwFwLess\components\ratelimit;

class RateLimitFactory
{
    const ALGORITHM_LEAKY_BUCKET = 'leaky_bucket';
    const ALGORITHM_ETCD_LEAKY_BUCKET = 'etcd_leaky_bucket';
    const ALGORITHM_SLIDING_WINDOW = 'sliding_window';

    public static $resolvers = [
        self::ALGORITHM_LEAKY_BUCKET => [
            [RateLimit::class, 'create']
        ],
        self::ALGORITHM_ETCD_LEAKY_BUCKET => [
            [\SwFwLess\components\etcd\RateLimit::class, 'create']
        ],
        self::ALGORITHM_SLIDING_WINDOW => [
            [SlidingWindow::class, 'create']
        ],
    ];

    /**
     * @param string $algorithm
     * @return RateLimit|SlidingWindow|\SwFwLess\components\etcd\RateLimit
     */
    public static function resolve($algorithm = self::ALGORITHM_LEAKY_BUCKET)
    {
        return call_user_func(self::$resolvers[$algorithm]);
    }
}
