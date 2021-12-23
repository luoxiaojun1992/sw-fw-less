<?php

namespace SwFwLessTests\components\log;

use SwFwLess\components\Config;
use SwFwLess\facades\Log;

class LogBench
{
    public function benchLog()
    {
        $logPath = __DIR__ . '/../../output/bench_log.log';
        if (file_exists($logPath)) {
            unlink($logPath);
        }
        Config::set('log.switch', true);
        \SwFwLess\components\log\Log::create($logPath);

        for ($i = 0; $i < 1000; ++$i) {
            Log::info('bench log ' . ((string)$i));
        }

        Config::clear();
        if (file_exists($logPath)) {
            unlink($logPath);
        }
    }
}
