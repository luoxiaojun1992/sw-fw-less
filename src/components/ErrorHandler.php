<?php

namespace SwFwLess\components;

use SwFwLess\components\http\Response;
use SwFwLess\exceptions\HttpException;
use Cake\Event\Event;

class ErrorHandler
{
    const EVENT_ERROR = 'error.error';

    public static function handle(\Throwable $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getCode();
        } else {
            $statusCode = 500;
        }

        static::logErrMsg($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        static::fireErrEvent($e);

        return Response::output(static::formatErrMsg($e), $statusCode);
    }

    private static function logErrMsg($errMsg)
    {
        \SwFwLess\facades\Log::err($errMsg);
    }

    private static function fireErrEvent(\Throwable $e)
    {
        event(new Event(static::EVENT_ERROR, null, [
            'error' => $e,
        ]));
    }

    private static function formatErrMsg(\Throwable $e)
    {
        return call_user_func_array(config('error_handler.err_formatter'), [$e]);
    }
}
