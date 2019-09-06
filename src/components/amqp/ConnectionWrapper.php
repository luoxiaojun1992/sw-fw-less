<?php

namespace SwFwLess\components\amqp;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPChannelException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPConnectionException;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Exception\AMQPProtocolConnectionException;
use PhpAmqpLib\Exception\AMQPProtocolException;

/**
 * Class ConnectionWrapper
 * @package SwFwLess\components\amqp
 * @mixin AMQPSocketConnection
 */
class ConnectionWrapper
{
    /** @var AMQPSocketConnection */
    private $connection;
    private $needRelease = true;

    /**
     * @return AMQPSocketConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
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
    private function callConnection($name, $arguments)
    {
        return call_user_func_array([$this->connection, $name], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     * @throws \Throwable
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->connection, $name)) {
            try {
                return $this->callConnection($name, $arguments);
            } catch (\Throwable $e) {
                if ($this->causedByLostConnection($e)) {
                    $this->handleCommandException($e);
                    return $this->callConnection($name, $arguments);
                }

                throw $e;
            }
        }

        return null;
    }

    /**
     * @param \Throwable $e
     * @return bool
     */
    public function causedByLostConnection(\Throwable $e) {
        if (($e instanceof AMQPConnectionClosedException)
            || ($e instanceof AMQPConnectionException)
            || ($e instanceof AMQPProtocolConnectionException)
            || ($e instanceof AMQPChannelClosedException)
            || ($e instanceof AMQPChannelException)
            || ($e instanceof AMQPProtocolChannelException)
            || ($e instanceof AMQPProtocolException)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Throwable $e
     */
    private function handleCommandException(\Throwable $e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->getConnection()->reconnect();
        }
    }
}
