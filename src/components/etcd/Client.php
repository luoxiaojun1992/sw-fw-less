<?php

namespace SwFWLess\components\etcd;

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
        list($leaseGrantResponse, $status) = $this->getLeaseClient()->LeaseGrant(
            (new LeaseGrantRequest())->setTTL($ttl)
        );
        if ($status === 0) {
            return $leaseGrantResponse->getID();
        }
        
        return null;
    }

    protected function leaseKeepAlive($id)
    {
        list(, $status) = $this->getLeaseClient()->LeaseKeepAlive(
            (new LeaseKeepAliveRequest())->setID($id)
        );
        if ($status === 0) {
            return true;
        }

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
        
        list(, $status) = $this->getKvClient()->Put(
            (new PutRequest())->setKey($key)
                ->setValue($value)
                ->setLease($leaseId)
        );

        if ($status === 0) {
            return true;
        }

        return false;
    }

    public function get($key)
    {
        list($rangeResponse, $status) = $this->getKvClient()->Range(
            (new RangeRequest())->setKey($key)
                ->setRangeEnd('\0')
                ->setLimit(1)
        );

        if ($status === 0) {
            foreach($rangeResponse->getKvs() as $kv) {
                return $kv->getValue();
            }
        }

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
        list($rangeResponse, $status) = $this->getKvClient()->Range(
            (new RangeRequest())->setKey($key)
                ->setRangeEnd('\0')
                ->setLimit(1)
        );

        if ($status === 0) {
            foreach($rangeResponse->getKvs() as $kv) {
                $leaseId = $kv->getLease();
                if ($leaseId > 0) {
                    return $this->leaseKeepAlive($leaseId);
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    public function expire($key, $ttl)
    {
        $leaseId = $this->createLease($ttl);
        if (is_null($leaseId)) {
            return false;
        }

        list(, $status) = $this->getKvClient()->Put(
            (new PutRequest())->setKey($key)
                ->setIgnoreValue(true)
                ->setLease($leaseId)
        );

        if ($status === 0) {
            return true;
        }

        return false;
    }

    public function del($key)
    {
        list($deleteRangeResponse, $status) = $this->getKvClient()->DeleteRange(
            (new DeleteRangeRequest())->setKey($key)
                ->setRangeEnd('\0')
        );

        if ($status === 0) {
            return $deleteRangeResponse->getDeleted() === 1;
        }

        return false;
    }

    public function lock($key, $ttl = 0)
    {
        if (!is_null($this->get($key))) {
            $ttl = 0;
        }

        $leaseId = 0;
        if ($ttl > 0) {
            $leaseId = $this->createLease($ttl);
            if (is_null($leaseId)) {
                return false;
            }
        }

        list($putResponse, $status) = $this->getKvClient()->Put(
            (new PutRequest())->setKey($key)
                ->setValue('lock')
                ->setPrevKv(true)
                ->setLease($leaseId)
        );

        if ($status === 0) {
            return $putResponse->getPrevKv() === null;
        }

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
            $ttl = 0;
        }

        $leaseId = 0;
        if ($ttl > 0) {
            $leaseId = $this->createLease($ttl);
            if (is_null($leaseId)) {
                return false;
            }
        }

        list($putResponse, $status) = $this->getKvClient()->Put(
            (new PutRequest())->setKey($key)
                ->setValue('lock')
                ->setPrevKv(true)
                ->setLease($leaseId)
        );

        if ($status === 0) {
            $prevKey = $putResponse->getPrevKv();
            return ($prevKey === null) ? 1 : ($prevKey->getVersion() + 1);
        }

        return false;
    }
}
