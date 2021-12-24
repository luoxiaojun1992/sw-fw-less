<?php

namespace SwFwLessTests\components\log;

use Monolog\Logger;
use SwFwLess\components\Config;
use SwFwLess\facades\Log;

class LogBench
{
    public function benchCoStreamLog()
    {
        $logPath = __DIR__ . '/../../output/bench_co_stream_log.log';
        if (file_exists($logPath)) {
            unlink($logPath);
        }
        Config::set('log.switch', true);
        \SwFwLess\components\log\Log::create(
            $logPath,
            Logger::DEBUG,
            [],
            100,
            1000,
            'sw-fw-less',
            3,
            true,
            \SwFwLess\components\log\Log::DRIVER_CO_STREAM
        );

        for ($i = 0; $i < 10000; ++$i) {
            Log::info('bench log ' . ((string)$i));
        }

        \swoole_event::wait();

        \SwFwLess\components\log\Log::clearInstance();
        Config::clear();
        if (file_exists($logPath)) {
            unlink($logPath);
        }
    }

    public function benchMemoryMapLog()
    {
        $logPath = __DIR__ . '/../../output/bench_mmap_log.log';
        if (file_exists($logPath)) {
            unlink($logPath);
        }
        Config::set('log.switch', true);
        \SwFwLess\components\log\Log::create(
            $logPath,
            Logger::DEBUG,
            [],
            100,
            1000,
            'sw-fw-less',
            3,
            true,
            \SwFwLess\components\log\Log::DRIVER_MEMORY_MAP
        );

        for ($i = 0; $i < 10000; ++$i) {
            Log::info('bench log ' . ((string)$i));
        }

        \SwFwLess\components\log\Log::clearInstance();
        Config::clear();
        if (file_exists($logPath)) {
            unlink($logPath);
        }
    }
}
