<?php

namespace App\components\amqp;

use App\components\Config;
use Cake\Event\Event as CakeEvent;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Wire\IO\SocketIO;

class ConnectionPool
{
    private static $instance;

    /** @var ConnectionWrapper[] */
    private $connectionPool = [];

    private $config;

    /**
     * @return ConnectionPool|null
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('amqp.switch')) {
            return self::$instance = new self();
        } else {
            return null;
        }
    }

    /**
     * ConnectionPool constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('amqp');

        class_alias(CoroutineSocketIO::class, SocketIO::class);

        $poolSize = $this->config['pool_size'];
        for ($i = 0; $i < $poolSize; ++$i) {
            $this->connectionPool[] = $this->getConnect();
        }

        if (Config::get('amqp.pool_change_event')) {
            event(
                new CakeEvent('amqp:pool:change',
                    null,
                    ['count' => $poolSize]
                )
            );
        }

        AMQPStreamWrapper::register();
    }

    /**
     * @param $name
     * @return string
     */
    public function getQueue($name)
    {
        return $this->config['prefix'] . $name;
    }

    /**
     * @return ConnectionWrapper mixed
     */
    public function pick()
    {
        $connection = array_pop($this->connectionPool);
        if (!$connection) {
            $connection = $this->getConnect(false);
        } else {
            if (Config::get('amqp.pool_change_event')) {
                event(
                    new CakeEvent('amqp:pool:change',
                        null,
                        ['count' => -1]
                    )
                );
            }
        }

        return $connection;
    }

    /**
     * @param ConnectionWrapper $connection
     */
    public function release($connection)
    {
        if ($connection) {
            if ($connection->isNeedRelease()) {
                $this->connectionPool[] = $connection;
                if (Config::get('amqp.pool_change_event')) {
                    event(
                        new CakeEvent('amqp:pool:change',
                            null,
                            ['count' => 1]
                        )
                    );
                }
            }
        }
    }

    public function __destruct()
    {
        foreach ($this->connectionPool as $connection) {
            $connection->close();
        }
    }

    /**
     * @param bool $needRelease
     * @return ConnectionWrapper
     */
    public function getConnect($needRelease = true)
    {
        $connection = new AMQPSocketConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['passwd'],
            $this->config['vhost'],
            false,
            'AMQPLAIN',
            null,
            $this->config['locale'],
            $this->config['read_timeout'],
            $this->config['keepalive'],
            $this->config['write_timeout'],
            $this->config['heartbeat']
        );
        $connection->channel($this->config['channel_id']);

        return (new ConnectionWrapper())->setConnection($connection)->setNeedRelease($needRelease);
    }

    /**
     * @return int
     */
    public function countPool()
    {
        return count($this->connectionPool);
    }
}
