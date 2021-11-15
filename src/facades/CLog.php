<?php

namespace SwFwLess\facades;

/**
 * class CLog
 *
 * @method static bool log($logPath, $content, $level = 0, $file = '', $line = 0)
 * @method static bool logTrace($logPath, $content, $file = '', $line = 0)
 * @method static bool logDebug($logPath, $content, $file = '', $line = 0)
 * @method static bool logInfo($logPath, $content, $file = '', $line = 0)
 * @method static bool logWarn($logPath, $content, $file = '', $line = 0)
 * @method static bool logError($logPath, $content, $file = '', $line = 0)
 * @method static bool logFatal($logPath, $content, $file = '', $line = 0)
 *
 * @package SwFwLess\facades
 */
class CLog extends AbstractFacade
{
    /**
     * @return \SwFwLess\components\log\ffi\Log|null
     * @throws \Exception
     */
    protected static function getAccessor()
    {
        return \SwFwLess\components\log\ffi\Log::create([]);
    }
}
