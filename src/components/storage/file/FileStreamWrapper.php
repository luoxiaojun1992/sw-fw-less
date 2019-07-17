<?php

namespace SwFwLess\components\storage\file;

class FileStreamWrapper
{
    private $path;
    private $mode;
    private $data;
    private $readPosition;
    private $writePosition;

    public static function register()
    {
        stream_wrapper_register('storage', __CLASS__);
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
        $this->path = $url['host'] . (isset($url['path']) ? $url['path'] : '');
        $this->mode = $mode;
        $this->readPosition = 0;
        $this->writePosition = 0;

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
            $this->data = \SwFwLess\facades\File::prepare()->read($this->path);
        }

        $ret = substr($this->data, $this->readPosition, $count);
        $this->readPosition += strlen($ret);
        return $ret;
    }

    /**
     * @return mixed
     */
    function stream_tell()
    {
        return $this->readPosition;
    }

    /**
     * @return bool
     */
    function stream_eof()
    {
        return is_null($this->data) ? false : ($this->readPosition >= strlen($this->data));
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
        $this->path = $url['host'] . (isset($url['path']) ? $url['path'] : '');

        $file = \SwFwLess\facades\File::prepare();
        if ($file->has($this->path)) {
            return [
                'dev'     => 0,
                'ino'     => 0,
                'mode'    => 33060,
                'nlink'   => 0,
                'uid'     => 0,
                'gid'     => 0,
                'rdev'    => 0,
                'size'    => $file->getSize($this->path),
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
//            $file = \SwFwLess\facades\File::prepare();
//            if ($file->has($this->path)) {
//                $exists = true;
//                $size = $file->getSize($this->path);
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
        if ($this->writePosition === 0) {
            $fileSystem = \SwFwLess\facades\File::prepare();
            if ($fileSystem->has($this->path)) {
                $fileSystem->delete($this->path);
            }
            $fileSystem->put($this->path, $data);
        } else {
            \SwFwLess\facades\File::prepare(FILE_APPEND)->put($this->path, $data);
        }

        $dataLen = strlen($data);
        $this->data .= $data;
        $this->writePosition += $dataLen;

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
        $this->path = $url['host'] . (isset($url['path']) ? $url['path'] : '');

        \SwFwLess\facades\File::prepare()->delete($this->path);

        return true;
    }
}
