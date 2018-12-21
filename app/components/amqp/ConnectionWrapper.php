<?php

namespace App\components\amqp;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPChannelException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPConnectionException;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Exception\AMQPProtocolConnectionException;
use PhpAmqpLib\Exception\AMQPProtocolException;

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
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->connection, $name)) {
            try {
                return $this->callConnection($name, $arguments);
            } catch (\Exception $e) {
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
     * @param \Exception $e
     * @return bool
     */
    public function causedByLostConnection(\Exception $e) {
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
     * @param \Exception $e
     */
    private function handleCommandException(\Exception $e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->getConnection()->reconnect();
        }
    }
}
