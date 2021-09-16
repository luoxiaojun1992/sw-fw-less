<?php

namespace SwFwLess\components\storage\file\mmap;

use League\Flysystem\Adapter\Local;
use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\Runtime;
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

        if (Runtime::supportFFI()) {
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
            "int WriteFile(const char *pathname, const char *content);" . PHP_EOL .
            "char * ReadFile(const char *pathname);" . PHP_EOL .
            "int AppendFile(const char *pathname, const char *content);",
            $ffiPath
        );
    }

    protected function nativeWriteFile($filepath, $content)
    {
        return File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            dirname($filepath)
        )->write(basename($filepath), $content);
    }

    protected function nativeAppendFile($filepath, $content)
    {
        $currentContent = $this->nativeReadFile($filepath);
        if ($currentContent === false) {
            return false;
        }
        return $this->nativeWriteFile(
            $filepath,
            $currentContent . $content
        );
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

    public function writeFile($filepath, $content)
    {
        if (!Runtime::supportFFI()) {
            return $this->nativeWriteFile($filepath, $content);
        }

        if (!$this->ffiPath) {
            return $this->nativeWriteFile($filepath, $content);
        }

        return !((bool)($this->udf->WriteFile($filepath, $content)));
    }

    public function readFile($filepath)
    {
        if (!Runtime::supportFFI()) {
            return $this->nativeReadFile($filepath);
        }

        if (!$this->ffiPath) {
            return $this->nativeReadFile($filepath);
        }

        return \FFI::string($this->udf->ReadFile($filepath));
    }

    public function appendFile($filepath, $content)
    {
        if (!Runtime::supportFFI()) {
            return $this->nativeAppendFile($filepath, $content);
        }

        if (!$this->ffiPath) {
            return $this->nativeAppendFile($filepath, $content);
        }

        return !((bool)($this->udf->AppendFile($filepath, $content)));
    }
}
