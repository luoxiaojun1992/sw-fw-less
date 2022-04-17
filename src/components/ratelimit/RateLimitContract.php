<?php

namespace SwFwLess\components\ratelimit;

interface RateLimitContract
{
    public function pass($metric, $period, $throttle, &$remaining = null);
}
