<?php

namespace SwFwLess\components\time\ntp;

use Bt51\NTP\Socket;
use SwFwLess\components\pool\AbstractPool;

class ClientPool extends AbstractPool
{
    protected static $instance;

    protected $config = [];

    /**
     * @param null $config
     * @return ClientPool|null
     */
    public static function create($config = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (is_array($config) && !empty($config['switch'])) {
            return self::$instance = new self($config);
        } else {
            return null;
        }
    }

    /**
     * ObjectPool constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);

        foreach ($config['servers'] as $serverId => $options) {
            for ($i = 0; $i < $options['pool_size']; ++$i) {
                $this->pool[$serverId][] = $this->createRes($serverId);
            }
        }
    }

    public function createSocket($id)
    {
        $options = $this->config['servers'][$id];

        return new Socket(
            $options['host'],
            $options['port'] ?? 123,
            $options['timeout'] ?? 5
        );
    }

    protected function createRes($id)
    {
        $options = $this->config['servers'][$id];

        return (new ClientWrapper($this->createSocket($id)))
            ->setIdleTimeout($options['idle_timeout'] ?? 500)
            ->setLastActivityAt();
    }

    protected function pickServerId()
    {
        return array_rand($this->config['servers'], 1);
    }

    /**
     * @return ClientWrapper|null
     */
    public function pickAnyId()
    {
        return parent::pick($this->pickServerId());
    }
}
