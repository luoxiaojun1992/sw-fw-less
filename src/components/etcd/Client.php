<?php

namespace SwFWLess\components\etcd\;

use Etcdserverpb\KVClient;
use Etcdserverpb\LeaseClient;
use Etcdserverpb\LeaseGrantRequest;
use Etcdserverpb\PutRequest;
use Etcdserverpb\RangeRequest;

class Client
{
    protected $endpoint;

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
}
