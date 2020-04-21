<?php

namespace SwFwLess\components\etcd;

use SwFwLess\components\swoole\coresource\traits\CoroutineRes;

class Lock
{
    use CoroutineRes;

    /**
     * @var Client
     */
    private $etcd;

    private $config = [
        'lock_prefix' => 'lock:',
    ];

    private $locked_keys = [];

    /**
     * @param Client|null $etcd
     * @param array $config
     * @return mixed|Lock|null
     */
    public static function create(Client $etcd = null, $config = [])
    {
        if ($instance = self::fetch()) {
            return $instance;
        }

        if (!is_null($etcd)) {
            return new self($etcd, $config);
        }

        return null;
    }

    /**
     * Lock constructor.
     * @param Client $etcd
     * @param array $config
     */
    public function __construct(Client $etcd, $config = [])
    {
        $this->etcd = $etcd;
        $this->config = array_merge($this->config, $config);
        self::register($this);
    }

    protected function lockPrefix()
    {
        return $this->config['lock_prefix'];
    }

    protected function lockKeyWithPrefix($lockKey)
    {
        return $this->lockPrefix() . $lockKey;
    }

    /**
     * Add a lock
     *
     * @param     $key
     * @param     int $ttl
     * @param     bool $guard
     * @param     callable|null $callback
     * @return    mixed
     * @throws \Throwable
     */
    public function lock($key, $ttl = 0, $guard = false, $callback = null)
    {
        $deferTimerId = null;
        $result = $this->etcd->lock($this->lockKeyWithPrefix($key), $ttl);
        if ($result) {
            $this->addLockedKey($key, $guard);

            if (is_callable($callback)) {
                //Defer
                if ($ttl >= 2) {
                    $deferTimerId = swoole_timer_tick(1000, function () use ($key) {
                        $this->etcd->defer($this->lockKeyWithPrefix($key));
                    });
                }

                $callbackRes = call_user_func($callback);
                $this->unlock($key);
                swoole_timer_clear($deferTimerId);
                return $callbackRes;
            }

            return true;
        }

        return false;
    }

    /**
     * Release a lock
     *
     * @param     $key
     * @return    bool
     * @throws \Throwable
     */
    public function unlock($key)
    {
        if (!empty($this->locked_keys[$key]['guard'])) {
            return false;
        }

        $result = $this->etcd->del($this->lockKeyWithPrefix($key));
        if ($result) {
            unset($this->locked_keys[$key]);
            return true;
        }
        return false;
    }

    /**
     * Defer a lock
     *
     * @param $key
     * @param $ttl
     * @return bool
     */
    public function defer($key, $ttl)
    {
        return $this->etcd->expire($this->lockKeyWithPrefix($key), $ttl);
    }

    private function addLockedKey($key, $guard = false)
    {
        $this->locked_keys[$key] = [
            'key' => $key,
            'guard' => $guard,
        ];
    }

    /**
     * Flush all locks
     * @throws \Throwable
     */
    public function flushAll()
    {
        foreach ($this->locked_keys as $locked_key) {
            if (!$locked_key['guard']) {
                $this->unlock($locked_key['key']);
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public function __destruct()
    {
        $this->flushAll();
    }
}
