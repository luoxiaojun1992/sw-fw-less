<?php

namespace SwFwLess\facades;

use Monolog\Logger;

/**
 * Class Log
 *
 * @method static bool log($level, $message, array $context = array())
 * @method static bool debug($message, array $context = array())
 * @method static bool info($message, array $context = array())
 * @method static bool notice($message, array $context = array())
 * @method static bool warn($message, array $context = array())
 * @method static bool warning($message, array $context = array())
 * @method static bool err($message, array $context = array())
 * @method static bool error($message, array $context = array())
 * @method static bool crit($message, array $context = array())
 * @method static bool critical($message, array $context = array())
 * @method static bool alert($message, array $context = array())
 * @method static bool emerg($message, array $context = array())
 * @method static bool emergency($message, array $context = array())
 * @method static Logger getLogger()
 * @method static int countRecordBuffer()
 * @method static int countPool()
 * @method static flush()
 * @package SwFwLess\facades
 */
class Log extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\log\Log::create();
    }
}
