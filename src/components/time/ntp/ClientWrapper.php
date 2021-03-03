<?php

namespace SwFwLess\components\time\ntp;

use Bt51\NTP\Client;
use SwFwLess\components\pool\Poolable;

class ClientWrapper extends Client implements Poolable
{
    protected $serverId;

    protected $needRelease = false;

    public function reset()
    {
        $this->socket->close();
        return $this;
    }

    public function needRelease()
    {
        return $this->needRelease();
    }

    public function setReleaseToPool(bool $releaseToPool)
    {
        $this->needRelease = $releaseToPool;
        return $this;
    }

    public function getPoolResId()
    {
        return $this->serverId;
    }
}
