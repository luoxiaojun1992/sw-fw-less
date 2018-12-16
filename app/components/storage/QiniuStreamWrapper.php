<?php

namespace App\components\storage;

use function Swlib\Http\str;

class QiniuStreamWrapper
{
    private $host;
    private $path;
    private $mode;
    private $data;
    private $position;

    public static function register()
    {
        stream_wrapper_register('qiniu', __CLASS__);
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
        $this->path = $url['path'];
        $this->mode = $mode;
        $this->position = 0;

        return true;
    }

    /**
     * @param $count
     * @return bool|string
     * @throws \Exception
     */
    function stream_read($count)
    {
        if (is_null($this->data)) {
            $this->data = \App\facades\Qiniu::prepare($this->host)->read($this->path);
        }

        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
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
     * @throws \League\Flysystem\FileNotFoundException
     */
    function url_stat($path, $flags)
    {
        $url = parse_url($path);
        $this->host = $url['host'];
        $this->path = $url['path'];

        $qiniu = \App\facades\Qiniu::prepare($this->host);
        if ($qiniu->has($this->path)) {
            return [
                'dev'     => 0,
                'ino'     => 0,
                'mode'    => 33060,
                'nlink'   => 0,
                'uid'     => 0,
                'gid'     => 0,
                'rdev'    => 0,
                'size'    => $qiniu->getSize($this->path),
                'atime'   => 0,
                'mtime'   => 0,
                'ctime'   => 0,
                'blksize' => 0,
                'blocks'  => 0
            ];
        }

        return 0;
    }

    /**
     * @return array|int
     * @throws \League\Flysystem\FileNotFoundException
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
//            $qiniu = \App\facades\Qiniu::prepare($this->host);
//            if ($qiniu->has($this->path)) {
//                $exists = true;
//                $size = $qiniu->getSize($this->path);
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
     * @throws \Exception
     */
    function stream_write($data)
    {
        \App\facades\Qiniu::prepare($this->host)->write($this->path, $data);

        $this->data = $data;

        return strlen($data);
    }

    /**
     * @param $path
     * @return bool
     * @throws \Exception
     */
    function unlink($path)
    {
        $url = parse_url($path);
        $this->host = $url['host'];
        $this->path = $url['path'];

        \App\facades\Qiniu::prepare($this->host)->delete($this->path);

        return true;
    }
}
