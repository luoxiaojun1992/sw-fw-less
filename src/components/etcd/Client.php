<?php

namespace SwFwLess\components\etcd;

use Etcdserverpb\DeleteRangeRequest;
use Etcdserverpb\KVClient;
use Etcdserverpb\LeaseClient;
use Etcdserverpb\LeaseGrantRequest;
use Etcdserverpb\LeaseKeepAliveRequest;
use Etcdserverpb\PutRequest;
use Etcdserverpb\RangeRequest;

class Client
{
    private static $instance;

    protected $endpoint;

    public static function create($config = [])
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (!empty($config)) {
            return self::$instance = new self($config);
        }

        return null;
    }

    public function __construct($config)
    {
        $this->endpoint = $config['endpoint'];
    }

    protected function getKvClient()
    {
        return new KVClient($this->endpoint);
    }

    protected function getLeaseClient()
    {
        return new LeaseClient($this->endpoint);
    }

    protected function createLease($ttl)
    {
        $leaseClient = $this->getLeaseClient();
        $leaseClient->start();
        list($leaseGrantResponse, $status) = $leaseClient->LeaseGrant(
            (new LeaseGrantRequest())->setTTL($ttl)
        );
        if ($status === 0) {
            $leaseClient->close();
            return $leaseGrantResponse->getID();
        }

        $leaseClient->close();
        return null;
    }

    protected function leaseKeepAlive($id)
    {
        $leaseClient = $this->getLeaseClient();
        $leaseClient->start();
        list(, $status) = $leaseClient->LeaseKeepAlive(
            (new LeaseKeepAliveRequest())->setID($id)
        );
        if ($status === 0) {
            $leaseClient->close();
            return true;
        }

        $leaseClient->close();
        return false;
    }

    public function put($key, $value, $ttl = 0)
    {
        $leaseId = 0;
        if ($ttl > 0) {
            $leaseId = $this->createLease($ttl);
            if (is_null($leaseId)) {
                return false;
            }
        }

        $kvClient = $this->getKvClient();
        $kvClient->start();
        list(, $status) = $kvClient->Put(
            (new PutRequest())->setKey($key)
                ->setValue($value)
                ->setLease($leaseId)
        );

        if ($status === 0) {
            $kvClient->close();
            return true;
        }

        $kvClient->close();
        return false;
    }

    public function get($key)
    {
        $kvClient = $this->getKvClient();
        $kvClient->start();
        list($rangeResponse, $status) = $kvClient->Range(
            (new RangeRequest())->setKey($key)
                ->setRangeEnd('\0')
                ->setLimit(1)
        );

        if ($status === 0) {
            foreach($rangeResponse->getKvs() as $kv) {
                $kvClient->close();
                return $kv->getValue();
            }
        }

        $kvClient->close();
        return null;
    }

    /**
     * @param $key
     * @throws \Exception
     */
    public function ttl($key)
    {
        throw new \Exception('TTL method of etcd is not supported.');
    }

    /**
     * @param $key
     * @return bool|null
     */
    public function defer($key)
    {
        $kvClient = $this->getKvClient();
        $kvClient->start();
        list($rangeResponse, $status) = $kvClient->Range(
            (new RangeRequest())->setKey($key)
                ->setRangeEnd('\0')
                ->setLimit(1)
        );

        if ($status === 0) {
            foreach($rangeResponse->getKvs() as $kv) {
                $leaseId = $kv->getLease();
                if ($leaseId > 0) {
                    $kvClient->close();
                    return $this->leaseKeepAlive($leaseId);
                } else {
                    $kvClient->close();
                    return false;
                }
            }
        }

        $kvClient->close();
        return false;
    }

    public function expire($key, $ttl)
    {
        $leaseId = $this->createLease($ttl);
        if (is_null($leaseId)) {
            return false;
        }

        $kvClient = $this->getKvClient();
        $kvClient->start();
        list(, $status) = $kvClient->Put(
            (new PutRequest())->setKey($key)
                ->setIgnoreValue(true)
                ->setLease($leaseId)
        );

        if ($status === 0) {
            $kvClient->close();
            return true;
        }

        $kvClient->close();
        return false;
    }

    public function del($key)
    {
        $kvClient = $this->getKvClient();
        $kvClient->start();
        list($deleteRangeResponse, $status) = $kvClient->DeleteRange(
            (new DeleteRangeRequest())->setKey($key)
                ->setRangeEnd('\0')
        );

        if ($status === 0) {
            $kvClient->close();
            return $deleteRangeResponse->getDeleted() === 1;
        }

        $kvClient->close();
        return false;
    }

    public function lock($key, $ttl = 0)
    {
        if (!is_null($this->get($key))) {
            $ttl = -1;
        }

        if ($ttl > 0) {
            $leaseId = $this->createLease($ttl);
            if (is_null($leaseId)) {
                return false;
            }
        } elseif ($ttl < 0) {
            $leaseId = -1;
        } else {
            $leaseId = 0;
        }

        $kvClient = $this->getKvClient();
        $kvClient->start();

        $putRequest = (new PutRequest())->setKey($key)
            ->setValue($key)
            ->setPrevKv(true);

        if ($leaseId >= 0) {
            $putRequest->setLease($leaseId);
        } elseif ($leaseId < 0) {
            $putRequest->setIgnoreLease(true);
        }

        list($putResponse, $status) = $kvClient->Put($putRequest);

        if ($status === 0) {
            $kvClient->close();
            return $putResponse->getPrevKv() === null;
        }

        $kvClient->close();
        return false;
    }

    /**
     * @param $key
     * @param int $ttl
     * @return bool|int
     */
    public function incr($key, $ttl = 0)
    {
        if (!is_null($this->get($key))) {
            $ttl = -1;
        }

        if ($ttl > 0) {
            $leaseId = $this->createLease($ttl);
            if (is_null($leaseId)) {
                return false;
            }
        } elseif ($ttl < 0) {
            $leaseId = -1;
        } else {
            $leaseId = 0;
        }

        $kvClient = $this->getKvClient();
        $kvClient->start();

        $putRequest = (new PutRequest())->setKey($key)
            ->setValue($key)
            ->setPrevKv(true);

        if ($leaseId >= 0) {
            $putRequest->setLease($leaseId);
        } elseif ($leaseId < 0) {
            $putRequest->setIgnoreLease(true);
        }

        list($putResponse, $status) = $kvClient->Put($putRequest);

        if ($status === 0) {
            $prevKey = $putResponse->getPrevKv();
            $kvClient->close();
            return ($prevKey === null) ? 1 : ($prevKey->getVersion() + 1);
        }

        $kvClient->close();
        return false;
    }
}
