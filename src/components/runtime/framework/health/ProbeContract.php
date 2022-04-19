<?php

namespace SwFwLess\components\runtime\framework\health;

interface ProbeContract
{
    public function health(): bool;
}
