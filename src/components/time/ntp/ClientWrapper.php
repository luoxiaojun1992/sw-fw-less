<?php

namespace SwFwLess\components\time\ntp;

use Bt51\NTP\Client;
use Carbon\Carbon;
use SwFwLess\components\Helper;
use SwFwLess\components\pool\Poolable;

class ClientWrapper extends Client implements Poolable
{
    protected $idleTimeout = 500; //seconds

    protected $lastActivityAt;

    protected $serverId;

    protected $needRelease = false;

    public function refresh()
    {
        if ($this->exceedIdleTimeout()) {
            $this->reconnect();
        }
    }

    public function reset()
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getIdleTimeout(): int
    {
        return $this->idleTimeout;
    }

    /**
     * @param int $idleTimeout
     * @return $this
     */
    public function setIdleTimeout(int $idleTimeout)
    {
        $this->idleTimeout = $idleTimeout;
        return $this;
    }

    /**
     * @param null|CarbonInterface $lastActivityAt
     * @return $this
     */
    public function setLastActivityAt($lastActivityAt = null)
    {
        $this->lastActivityAt = ($lastActivityAt ?: Carbon::now());
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastActivityAt()
    {
        return $this->lastActivityAt;
    }

    /**
     * @return bool
     */
    public function exceedIdleTimeout()
    {
        return (Carbon::now()->diffInSeconds($this->getLastActivityAt())) > ($this->getIdleTimeout());
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
     * Unpacks the binary data that was returned
     * from the remote ntp server
     *
     * @param string $binary The binary from the response
     *
     * @throws \Exception
     * @return string
     */
    protected function unpack($binary)
    {
        $data = unpack('N12', $binary);

        if (!isset($data[9])) {
            throw new \Exception('Connection timed out');
        }

        return sprintf('%u', $data[9]);
    }

    /**
     * @return \DateTime|mixed
     * @throws \Throwable
     */
    public function getTime()
    {
        $this->setLastActivityAt();

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
        $this->setLastActivityAt();
        return $this;
    }
}
