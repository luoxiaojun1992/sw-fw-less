<?php

namespace App\components\hbase;

use App\components\Helper;
use Hbase\HbaseClient;
use Thrift\Transport\TBufferedTransport;

class HbaseWrapper
{
    /** @var HbaseClient */
    private $client;
    /** @var TBufferedTransport */
    private $transport;
    private $needRelease = true;

    /**
     * @return HbaseClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return TBufferedTransport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedRelease()
    {
        return $this->needRelease;
    }

    /**
     * @param bool $needRelease
     * @return $this
     */
    public function setNeedRelease($needRelease)
    {
        $this->needRelease = $needRelease;
        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    private function callClient($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->client, $name)) {
            try {
                return $this->callClient($name, $arguments);
            } catch (\Exception $e) {
                if (Helper::causedByLostConnection($e)) {
                    $this->handleCommandException($e);
                    return $this->callClient($name, $arguments);
                }

                throw $e;
            }
        }

        return null;
    }

    /**
     * @param \Exception $e
     */
    private function handleCommandException(\Exception $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $hbaseWrapper = \App\facades\HbasePool::getConnect(false);
            $hbaseWrapper->getTransport()->open();
            $this->setClient($hbaseWrapper->getClient())->setTransport($hbaseWrapper->getTransport());
        }
    }
}
