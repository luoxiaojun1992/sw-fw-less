<?php

namespace SwFwLess\components\storage\file\mmap;

use League\Flysystem\Adapter\Local;
use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\runtime\php\FFI;
use SwFwLess\facades\File;

class MemoryMap
{
    protected $config = [];
    protected $ffiPath;
    protected $udf;

    /**
     * @param array $config
     * @return MemoryMap|static
     */
    public static function create($config = [])
    {
        return new self($config);
    }

    public function __construct($config = [])
    {
        $this->config = $config;

        if (FFI::support()) {
            $osType = OS::type();
            if ($osType === OS::OS_LINUX) {
                $this->ffiPath = __DIR__ . '/ffi/c/linux/libcmmap.so';
            } elseif ($osType === OS::OS_DARWIN) {
                $this->ffiPath = __DIR__ . '/ffi/c/darwin/libcmmap.so';
            }
        }

        if ($this->ffiPath) {
            $this->udf = $this->createUdf($this->ffiPath);
        }
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef(
            "int OpenFile(const char *pathname);" . PHP_EOL .
            "int CloseFile(int fd);" . PHP_EOL .
            "int WriteFileByFd(int fd, const char *content);" . PHP_EOL .
            "int WriteFile(const char *pathname, const char *content);" . PHP_EOL .
            "char * ReadFile(const char *pathname);" . PHP_EOL .
            "int AppendFileByFd(int fd, const char *content);" . PHP_EOL .
            "int AppendFile(const char *pathname, const char *content);",
            $ffiPath
        );
    }

    public function openFile($filepath)
    {
        return $this->udf->OpenFile($filepath);
    }

    public function closeFile($fd)
    {
        return $this->udf->CloseFile($fd);
    }

    public function writeFileByFd($fd, $content)
    {
        return $this->udf->WriteFileByFd($fd, $content);
    }

    public function appendFileByFd($fd, $content)
    {
        return $this->udf->AppendFileByFd($fd, $content);
    }

    protected function nativeWriteFile($filepath, $content)
    {
        return File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            dirname($filepath)
        )->put(basename($filepath), $content);
    }

    protected function nativeAppendFile($filepath, $content)
    {
        return File::prepare(
            LOCK_EX|FILE_APPEND,
            Local::DISALLOW_LINKS,
            [],
            dirname($filepath)
        )->put(basename($filepath), $content);
    }

    protected function nativeReadFile($filepath)
    {
        return File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            dirname($filepath)
        )->read(basename($filepath));
    }

    public function writeFile($filepath, $content, $native = false)
    {
        if ($native) {
            return $this->nativeWriteFile($filepath, $content);
        }

        if (!FFI::support()) {
            return $this->nativeWriteFile($filepath, $content);
        }

        if (!$this->ffiPath) {
            return $this->nativeWriteFile($filepath, $content);
        }

        return !((bool)($this->udf->WriteFile($filepath, $content)));
    }

    public function readFile($filepath, $native = false)
    {
        if ($native) {
            return $this->nativeReadFile($filepath);
        }

        if (!FFI::support()) {
            return $this->nativeReadFile($filepath);
        }

        if (!$this->ffiPath) {
            return $this->nativeReadFile($filepath);
        }

        return \FFI::string($this->udf->ReadFile($filepath));
    }

    public function appendFile($filepath, $content, $native = false)
    {
        if ($native) {
            return $this->nativeAppendFile($filepath, $content);
        }

        if (!FFI::support()) {
            return $this->nativeAppendFile($filepath, $content);
        }

        if (!$this->ffiPath) {
            return $this->nativeAppendFile($filepath, $content);
        }

        return !((bool)($this->udf->AppendFile($filepath, $content)));
    }
}
