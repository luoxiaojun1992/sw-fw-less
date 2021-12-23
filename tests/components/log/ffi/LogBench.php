<?php

namespace SwFwLessTests\components\log\ffi;

use SwFwLess\components\log\ffi\Log;
use SwFwLess\facades\CLog;

class LogBench
{
    public function benchCLog()
    {
        $logPath = __DIR__ . '/../../../output/bench_clog.log';
        if (file_exists($logPath)) {
            unlink($logPath);
        }

        for ($i = 0; $i < 1000; ++$i) {
            CLog::logInfo(
                $logPath, 'bench log ' . ((string)$i)
            );
        }

        unlink($logPath);
    }
}
