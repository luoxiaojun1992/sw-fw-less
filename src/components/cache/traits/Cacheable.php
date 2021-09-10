<?php

namespace SwFwLess\components\cache\traits;

trait Cacheable
{
    protected $withCache = false;

    protected $cacheKey;

    protected $ttl = 0;

    public function withCache($cacheKey, $ttl = 0)
    {
        $this->withCache = true;
        $this->cacheKey = $cacheKey;
        $this->ttl = $ttl;
        return $this;
    }
}
