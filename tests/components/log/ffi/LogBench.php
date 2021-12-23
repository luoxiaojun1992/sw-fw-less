<?php

namespace SwFwLessTests\components\log\ffi;

use SwFwLess\components\log\ffi\Log;
use SwFwLess\facades\CLog;

class LogBench
{
    public function benchLog()
    {
        $logPath = __DIR__ . '/../../../output/bench_clog.log';
        if (file_exists($logPath)) {
            unlink($logPath);
        }

        for ($i = 0; $i < 1000; ++$i) {
            CLog::log(
                $logPath, 'test log ' . ((string)$i), Log::LEVEL_INFO
            );
        }

        unlink($logPath);
    }
}
