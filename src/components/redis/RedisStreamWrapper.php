<?php

namespace SwFwLess\components\redis;

use SwFwLess\facades\RedisPool;

/**
 * Class RedisStreamWrapper
 * @package SwFwLess\components\redis
 */
class RedisStreamWrapper
{
    private $host;
    private $mode;
    private $data;
    private $position;

    public static function register()
    {
        stream_wrapper_register('redis', __CLASS__);
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
     * @param $count
     * @return bool|string
     * @throws \Throwable
     */
    function stream_read($count)
    {
        if (is_null($this->data)) {
            $result = false;
            /** @var \Redis $redis */
            $redis = RedisPool::pick();
            try {
                $result = $redis->get($this->host);
            } catch (\Throwable $e) {
                throw $e;
            } finally {
                RedisPool::release($redis);
            }
            $this->data = $result;
        }

        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $this->data !== false ? $ret : false;
    }

    /**
     * @return mixed
     */
    function stream_tell()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    function stream_eof()
    {
        return is_null($this->data) ? false : ($this->position >= strlen($this->data));
    }

    /**
     * @param $path
     * @param $flags
     * @return array|int
     * @throws \Throwable
     */
    function url_stat($path, $flags)
    {
        $url = parse_url($path);
        $this->host = $url['host'];

        /** @var \Redis $redis */
        $redis = RedisPool::pick();
        try {
            $redis->multi(\Redis::PIPELINE);
            $key = $this->host;
            $redis->exists($key);
            $redis->strlen($key);
            $res = $redis->exec();
            if ($res[0]) {
                static $modeMap = [
                    'r' => 33060,
                    'r+' => 33206,
                    'w' => 33188,
                    'rb' => 33060,
                ];

                return [
                    'dev' => 0,
                    'ino' => 0,
                    'mode' => $modeMap[$this->mode],
                    'nlink' => 0,
                    'uid' => 0,
                    'gid' => 0,
                    'rdev' => 0,
                    'size' => $res[1],
                    'atime' => 0,
                    'mtime' => 0,
                    'ctime' => 0,
                    'blksize' => 0,
                    'blocks' => 0
                ];
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }

        return 0;
    }

    /**
     * @return array|int
     * @throws \Throwable
     */
    public function stream_stat()
    {
        static $modeMap = [
            'r' => 33060,
            'r+' => 33206,
            'w' => 33188,
            'rb' => 33060,
        ];

        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => $modeMap[$this->mode],
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => strlen($this->data),
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0
        ];

//        $exists = false;
//        $size = 0;
//        if (!is_null($this->data)) {
//            $exists = true;
//            $size = strlen($this->data);
//        } else {
//            /** @var \Redis $redis */
//            $redis = RedisPool::pick();
//            try {
//                $redis->multi(\Redis::PIPELINE);
//                $key = $this->host;
//                $redis->exists($key);
//                $redis->strlen($key);
//                $res = $redis->exec();
//                if ($res[0]) {
//                    $exists = true;
//                    $size = $res[1];
//                }
//            } catch (\Throwable $e) {
//                throw $e;
//            } finally {
//                RedisPool::release($redis);
//            }
//        }
//
//        if ($exists) {
//            static $modeMap = [
//                'r' => 33060,
//                'r+' => 33206,
//                'w' => 33188,
//                'rb' => 33060,
//            ];
//
//            return [
//                'dev' => 0,
//                'ino' => 0,
//                'mode' => $modeMap[$this->mode],
//                'nlink' => 0,
//                'uid' => 0,
//                'gid' => 0,
//                'rdev' => 0,
//                'size' => $size,
//                'atime' => 0,
//                'mtime' => 0,
//                'ctime' => 0,
//                'blksize' => 0,
//                'blocks' => 0
//            ];
//        }
//
//        return 0;
    }

    /**
     * @param $data
     * @return int
     * @throws \Throwable
     */
    function stream_write($data)
    {
        /** @var \Redis $redis */
        $redis = RedisPool::pick();
        try {
            $redis->set($this->host, $data);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }

        $this->data = $data;

        return strlen($data);
    }

    /**
     * @param $path
     * @return bool
     * @throws \Throwable
     */
    function unlink($path)
    {
        $url = parse_url($path);
        $this->host = $url['host'];

        /** @var \Redis $redis */
        $redis = RedisPool::pick();
        try {
            $redis->del($this->host);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }

        return true;
    }
}
