<?php

namespace App\components\amqp;

use PhpAmqpLib\Exception\AMQPHeartbeatMissedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Exception\AMQPSocketException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Swoole\Coroutine\Socket;

class CoroutineSocketIO extends AbstractIO
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var float */
    protected $send_timeout;

    /** @var float */
    protected $read_timeout;

    /** @var int */
    protected $heartbeat;

    /** @var float */
    protected $last_read;

    /** @var float */
    protected $last_write;

    /** @var Socket */
    private $sock;

    /** @var bool */
    private $keepalive;

    /**
     * @param string $host
     * @param int $port
     * @param float $read_timeout
     * @param bool $keepalive
     * @param float|null $write_timeout if null defaults to read timeout
     * @param int $heartbeat how often to send heartbeat. 0 means off
     */
    public function __construct($host, $port, $read_timeout, $keepalive = false, $write_timeout = null, $heartbeat = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->read_timeout = $read_timeout;
        $this->send_timeout = $write_timeout ?: $read_timeout;
        $this->heartbeat = $heartbeat;
        $this->keepalive = $keepalive;
    }

    /**
     * Sets up the socket connection
     *
     * @throws \Exception
     */
    public function connect()
    {
        $this->sock = new Socket(AF_INET, SOCK_STREAM, 0);

        if (!$this->sock->connect($this->host, $this->port, max($this->read_timeout, $this->send_timeout))) {
            $errno = $this->sock->errCode;
            $errstr = 'Connection Error';
            throw new AMQPIOException(sprintf(
                'Error Connecting to server (%s): %s',
                $errno,
                $errstr
            ), $errno);
        }
    }

    /**
     * @return Socket
     */
    public function getSocket()
    {
        return $this->sock;
    }

    /**
     * Reconnects the socket
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    /**
     * @param int $n
     * @return mixed|string
     * @throws \PhpAmqpLib\Exception\AMQPIOException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     * @throws \PhpAmqpLib\Exception\AMQPSocketException
     */
    public function read($n)
    {
        if (!($this->sock instanceof Socket)) {
            throw new AMQPSocketException('Socket was null!');
        }
        $res = '';
        $read = 0;
        $buf = $this->sock->recvAll($n, $this->read_timeout);
        $res .= $buf;
        $read += mb_strlen($buf, 'ASCII');
        while ($read < $n && $buf !== '' && $buf !== false) {
            $this->check_heartbeat();

            $buf = $this->sock->recvAll($n - $read, $this->read_timeout);
            $res .= $buf;
            $read += mb_strlen($buf, 'ASCII');
        }

        if (mb_strlen($res, 'ASCII') != $n) {
            throw new AMQPIOException(sprintf(
                'Error reading data. Received %s instead of expected %s bytes',
                mb_strlen($res, 'ASCII'),
                $n
            ));
        }

        $this->last_read = microtime(true);

        return $res;
    }

    /**
     * @param string $data
     * @return void
     *
     * @throws \PhpAmqpLib\Exception\AMQPIOException
     * @throws \PhpAmqpLib\Exception\AMQPSocketException
     */
    public function write($data)
    {
        $len = mb_strlen($data, 'ASCII');

        while (true) {
            // Null sockets are invalid, throw exception
            if (!($this->sock instanceof Socket)) {
                throw new AMQPSocketException('Socket was null!');
            }

            $sent = $this->sock->sendAll($data, $this->send_timeout);
            if ($sent === false) {
                throw new AMQPIOException(sprintf(
                    'Error sending data. Last SocketError: %s',
                    $this->sock->errCode
                ));
            }

            // Check if the entire message has been sent
            if ($sent < $len) {
                // If not sent the entire message.
                // Get the part of the message that has not yet been sent as message
                $data = mb_substr($data, $sent, mb_strlen($data, 'ASCII') - $sent, 'ASCII');
                // Get the length of the not sent part
                $len -= $sent;
            } else {
                break;
            }
        }

        $this->last_write = microtime(true);
    }

    public function close()
    {
        if ($this->sock instanceof Socket) {
            $this->sock->close();
        }
        $this->sock = null;
        $this->last_read = null;
        $this->last_write = null;
    }

    /**
     * @param int $sec
     * @param int $usec
     * @return int|mixed
     */
    public function select($sec, $usec)
    {
        return true;
    }

    /**
     * Heartbeat logic: check connection health here
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function check_heartbeat()
    {
        // ignore unless heartbeat interval is set
        if ($this->heartbeat !== 0 && $this->last_read && $this->last_write) {
            $t = microtime(true);
            $t_read = round($t - $this->last_read);
            $t_write = round($t - $this->last_write);

            // server has gone away
            if (($this->heartbeat * 2) < $t_read) {
                $this->close();
                throw new AMQPHeartbeatMissedException("Missed server heartbeat");
            }

            // time for client to send a heartbeat
            if (($this->heartbeat / 2) < $t_write) {
                $this->write_heartbeat();
            }
        }
    }

    /**
     * Sends a heartbeat message
     */
    protected function write_heartbeat()
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $this->write($pkt->getvalue());
    }
}
