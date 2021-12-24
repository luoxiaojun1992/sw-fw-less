<?php

namespace SwFwLess\components\log\mmap;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use SwFwLess\components\storage\file\mmap\MemoryMap;
use SwFwLess\components\swoole\Scheduler;

/**
 * Stores to any stream resource
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @author Xiaojun Luo <luoxiaojun1992@sina.cn>
 */
class MonologMmapHandler extends AbstractProcessingHandler
{
    protected $stream;
    protected $url;
    private $errorMessage;
    protected $filePermission;
    private $dirCreated;
    private $recordBuffer = [];
    private $recordBufferMaxSize = 10;
    protected $syncLevels = [];

    /**
     * @param resource|string $stream
     * @param int             $level                    The minimum logging level at which this handler will be triggered
     * @param array           $syncLevels               The logging levels at one of which the log will be flushed immediately
     * @param Boolean         $bubble                   Whether the messages that are handled can bubble up the stack or not
     * @param int|null        $filePermission           Optional file permissions (default (0644) are only for owner read/write)
     * @param int             $recordBufferMaxSize   Max size of record buffer
     *
     * @throws \Exception                If a missing directory is not buildable
     * @throws \InvalidArgumentException If stream is not a resource or string
     */
    public function __construct(
        $stream,
        $level = Logger::DEBUG,
        $syncLevels = [],
        $bubble = true,
        $filePermission = null,
        $recordBufferMaxSize = 10
    )
    {
        parent::__construct($level, $bubble);

        $this->syncLevels = $syncLevels;

        if (is_string($stream)) {
            $this->url = $stream;
        } else {
            throw new \InvalidArgumentException('A stream must be a string.');
        }

        $this->filePermission = $filePermission;

        $this->createDir();

        $this->recordBufferMaxSize = $recordBufferMaxSize;
    }

    public function flush()
    {
        if (count($this->recordBuffer) > 0) {
            $this->write([], true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->flush();
    }

    /**
     * Return the stream URL if it was configured with a URL and not an active resource
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     * @param bool $flushAll
     * @throws \Exception
     */
    protected function write(array $record, $flushAll = false)
    {
        if (null === $this->url || '' === $this->url) {
            throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
        }

        $level = $record['level'] ?? null;

        if (is_null($level)) {
            $isSyncLevel = false;
        } else {
            $isSyncLevel = in_array($level, $this->syncLevels);
        }

        if (!$isSyncLevel) {
            if (count($record) > 0) {
                $this->recordBuffer[] = $record;
            }
            $recordBufferCount = $this->countRecordBuffer();
            if (!$flushAll && $recordBufferCount < $this->recordBufferMaxSize) {
                return;
            }
            if ($recordBufferCount <= 0) {
                return;
            }
        }

        $memoryMap = MemoryMap::create([]);

        $stream = $this->prepareWrite($memoryMap);

        if ($isSyncLevel) {
            $records = [$record];
        } else {
            $records = Scheduler::withoutPreemptive(function () use ($record) {
                return array_splice($this->recordBuffer, 0);
            });
        }

        try {
            $this->streamWrite($stream, $records, $memoryMap);
        } catch (\Exception $writeEx) {
            foreach ($records as $record) {
                Scheduler::withoutPreemptive(function () use ($record) {
                    array_push($this->recordBuffer, $record);
                });
            }
            throw $writeEx;
        } finally {
            $memoryMap->closeFile($stream);
        }
    }

    /**
     * Prepare for write
     *
     * @param MemoryMap $memoryMap
     * @return array
     */
    private function prepareWrite(MemoryMap $memoryMap)
    {
        $this->createDir();

        $this->errorMessage = null;
        set_error_handler(array($this, 'customErrorHandler'));
        if ($this->filePermission !== null) {
            @chmod($this->url, $this->filePermission);
        }
        restore_error_handler();

        return $memoryMap->openFile($this->url);
    }

    /**
     * Write to stream
     *
     * @param $stream
     * @param array $records
     * @param MemoryMap $memoryMap
     * @throws \Exception
     */
    protected function streamWrite($stream, array $records, MemoryMap $memoryMap)
    {
        if (count($records) <= 0) {
            return;
        }

        $logContent = '';
        foreach ($records as $record) {
            $logContent .= (string)$record['formatted'];
        }

        if ($memoryMap->appendFileByFd($stream, $logContent)) {
            throw new \Exception('mmap appending error');
        }
    }

    public function customErrorHandler($code, $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }

    /**
     * @param string $stream
     *
     * @return null|string
     */
    private function getDirFromStream($stream)
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if ('file://' === substr($stream, 0, 7)) {
            return dirname(substr($stream, 7));
        }

        return;
    }

    private function createDir()
    {
        // Do not try to create dir if it has already been tried.
        if ($this->dirCreated) {
            return;
        }

        $dir = $this->getDirFromStream($this->url);
        if (null !== $dir && !is_dir($dir)) {
            $this->errorMessage = null;
            set_error_handler(array($this, 'customErrorHandler'));
            $status = mkdir($dir, 0777, true);
            restore_error_handler();
            if (false === $status) {
                throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and its not buildable: '.$this->errorMessage, $dir));
            }
        }
        $this->dirCreated = true;
    }

    /**
     * @return int
     */
    public function countRecordBuffer()
    {
        return count($this->recordBuffer);
    }

    /**
     * @return null
     */
    public function getStreamPool()
    {
        return null;
    }
}
