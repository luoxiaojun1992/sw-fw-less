<?php

namespace App\components\hbase;

use App\components\Config;
use App\components\thrift\TCoroutineSocket;
use App\facades\File;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Hbase\HbaseClient;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;

class HbasePool
{
    private static $instance;

    /** @var HbaseWrapper[] */
    private $connectionPool = [];

    private $config;

    /**
     * @return HbasePool|null
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('hbase.switch')) {
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
        $this->config = Config::get('hbase');

        $poolSize = $this->config['pool_size'];
        for ($i = 0; $i < $poolSize; ++$i) {
            $this->connectionPool[] = $this->getConnect();
        }

        if (Config::get('hbase.pool_change_event')) {
            EventManager::instance()->dispatch(
                new Event('hbase:pool:change',
                    null,
                    ['count' => $poolSize]
                )
            );
        }
    }

    /**
     * @return HbaseWrapper mixed
     */
    public function pick()
    {
        $connection = array_pop($this->connectionPool);
        if (!$connection) {
            $connection = $this->getConnect(false);
        } else {
            if (Config::get('hbase.pool_change_event')) {
                EventManager::instance()->dispatch(
                    new Event('hbase:pool:change',
                        null,
                        ['count' => -1]
                    )
                );
            }
        }

        $connection->getTransport()->open();
        return $connection;
    }

    /**
     * @param HbaseWrapper $connection
     */
    public function release($connection)
    {
        if ($connection) {
            if ($connection->isNeedRelease()) {
                if ($connection->getTransport()->isOpen()) {
                    $connection->getTransport()->close();
                }
                $this->connectionPool[] = $connection;
                if (Config::get('hbase.pool_change_event')) {
                    EventManager::instance()->dispatch(
                        new Event('hbase:pool:change',
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
            if ($connection->getTransport()->isOpen()) {
                $connection->getTransport()->close();
            }
        }
    }

    /**
     * @param bool $needRelease
     * @return HbaseWrapper
     */
    public function getConnect($needRelease = true)
    {
        require_once File::path('/app/components/hbase/thrift/Hbase.php');
        require_once File::path('/app/components/hbase/thrift/Types.php');

        if (extension_loaded('swoole')) {
            $socket = new TCoroutineSocket($this->config['host'], $this->config['port']);
        } else {
            $socket = new TSocket($this->config['host'], $this->config['port']);
        }
        $socket->setSendTimeout($this->config['write_timeout']);
        $socket->setRecvTimeout($this->config['read_timeout']);

        $transport = new TBufferedTransport($socket);
        $protocol = new TBinaryProtocol($transport);
        $client = new HbaseClient($protocol);

        return (new HbaseWrapper())->setClient($client)->setTransport($transport)->setNeedRelease($needRelease);
    }

    /**
     * @return int
     */
    public function countPool()
    {
        return count($this->connectionPool);
    }
}
