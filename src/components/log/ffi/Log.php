<?php

namespace SwFwLess\components\log\ffi;

use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\runtime\PHPRuntime;

class Log
{
    const LEVEL_TRACE = 0;
    const LEVEL_DEBUG = 1;
    const LEVEL_INFO = 2;
    const LEVEL_WARN = 3;
    const LEVEL_ERROR = 4;
    const LEVEL_FATAL = 5;

    protected $config = [];
    protected $ffiPath;
    protected $udf;

    /**
     * @param array $config
     * @return Log|static
     */
    public static function create($config = [])
    {
        return new self($config);
    }

    public function __construct($config = [])
    {
        $this->config = $config;

        if (PHPRuntime::supportFFI()) {
            $osType = OS::type();
            if ($osType === OS::OS_LINUX) {
                $this->ffiPath = __DIR__ . '/c/build/linux/libclog.so';
            } elseif ($osType === OS::OS_DARWIN) {
                $this->ffiPath = __DIR__ . '/c/build/darwin/libclog.so';
            }
        }

        if ($this->ffiPath) {
            $this->udf = $this->createUdf($this->ffiPath);
        }
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef(
            "int Log(const char *logPath, int level, const char *file, int line, const char *content);",
            $ffiPath
        );
    }

    public function log($logPath, $content, $level = self::LEVEL_TRACE, $file = '', $line = 0)
    {
        return !((bool)($this->udf->Log($logPath, $level, $file, $line, $content)));
    }

    public function logTrace($logPath, $content, $file = '', $line = 0)
    {
        return $this->log(
            $logPath, $content, static::LEVEL_TRACE, $file, $line
        );
    }

    public function logDebug($logPath, $content, $file = '', $line = 0)
    {
        return $this->log(
            $logPath, $content, static::LEVEL_DEBUG, $file, $line
        );
    }

    public function logInfo($logPath, $content, $file = '', $line = 0)
    {
        return $this->log(
            $logPath, $content, static::LEVEL_INFO, $file, $line
        );
    }

    public function logWarn($logPath, $content, $file = '', $line = 0)
    {
        return $this->log(
            $logPath, $content, static::LEVEL_WARN, $file, $line
        );
    }

    public function logError($logPath, $content, $file = '', $line = 0)
    {
        return $this->log(
            $logPath, $content, static::LEVEL_ERROR, $file, $line
        );
    }

    public function logFatal($logPath, $content, $file = '', $line = 0)
    {
        return $this->log(
            $logPath, $content, static::LEVEL_FATAL, $file, $line
        );
    }
}
