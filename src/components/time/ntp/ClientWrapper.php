<?php

namespace SwFwLess\components\time\ntp;

use Bt51\NTP\Client;
use SwFwLess\components\Helper;
use SwFwLess\components\pool\Poolable;

class ClientWrapper extends Client implements Poolable
{
    protected $serverId;

    protected $needRelease = false;

    public function reset()
    {
        return $this;
    }

    public function needRelease()
    {
        return $this->needRelease;
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

    /**
     * @return \DateTime|mixed
     * @throws \Throwable
     */
    public function getTime()
    {
        $requestFunc = function () {
            $packet = $this->buildPacket();
            $this->write($packet);

            $time = $this->unpack($this->read());
            $time -= 2208988800;
            return \DateTime::createFromFormat('U', $time, new \DateTimeZone('UTC'));
        };

        try {
            return call_user_func($requestFunc);
        } catch (\Throwable $e) {
            if (Helper::causedByLostConnection($e)) {
                $this->reconnect();
                return call_user_func($requestFunc);
            }

            throw $e;
        }
    }

    public function reconnect()
    {
        $this->socket = ClientPool::create()->createSocket($this->serverId);
        return $this;
    }
}
