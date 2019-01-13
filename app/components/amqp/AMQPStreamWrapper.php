<?php

namespace App\components\amqp;

use App\components\Config;
use App\facades\AMQPConnectionPool;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AMQPStreamWrapper
 * @package App\components\amqp
 */
class AMQPStreamWrapper
{
    private $host;
    private $mode;
    private $data;
    private $position;

    public static function register()
    {
        stream_wrapper_register('amqp', __CLASS__);
    }

    /**
     * @param $path
     * @param $mode
     * @param $options
     * @param $opened_path
     * @return bool
     */
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->host = $url['host'];
        $this->mode = $mode;
        $this->position = 0;

        return true;
    }

    /**
     * @param $data
     * @return int
     * @throws \Exception
     */
    function stream_write($data)
    {
        $queueName = AMQPConnectionPool::getQueue($this->host);
        $do = function ($channel) use ($queueName, $data) {
            $channel->queue_declare(
                $queueName,
                false,
                true,
                false,
                false
            );
            $msg = new AMQPMessage($data);
            $channel->basic_publish($msg, '', $queueName);
        };

        $channel = null;
        $channel_id = Config::get('amqp.channel_id');
        /** @var AMQPSocketConnection|ConnectionWrapper $connection */
        $connection = AMQPConnectionPool::pick();
        try {
            $channel = $connection->channel($channel_id);
            $do($channel);
        } catch (\Exception $e) {
            if ($connection->causedByLostConnection($e)) {
                $realConnection = $connection->getConnection();
                $realConnection->reconnect();
                $channel = $realConnection->channel($channel_id);
                $do($channel);
            }
            throw $e;
        } finally {
            AMQPConnectionPool::release($connection);
        }

        $this->data = $data;

        return strlen($data);
    }
}
