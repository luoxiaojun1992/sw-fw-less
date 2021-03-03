<?php

namespace SwFwLess\components\time\ntp;

class Time
{
    protected $clientPool;

    protected $config = [];

    /** @var static */
    protected static $instance;

    public static function clearInstance()
    {
        static::$instance = null;
    }

    /**
     * @param array|null $config
     * @return static|null
     */
    public static function create($config = null)
    {
        if (static::$instance instanceof self) {
            return static::$instance;
        }

        if (is_array($config) && !empty($config['switch'])) {
            return static::$instance = new static($config);
        } else {
            return null;
        }
    }

    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
        $this->clientPool = ClientPool::create($config);
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function getTimestamp()
    {
        $client = $this->clientPool->pickAnyId();
        try {
            return $client->getTime()->getTimestamp();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->clientPool->release($client);
        }
    }
}
