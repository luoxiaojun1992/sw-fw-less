<?php

namespace App\components;

use App\components\http\Response;
use App\exceptions\HttpException;
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
        $errMsg = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        \App\facades\Log::err($errMsg);
        event(new Event(static::EVENT_ERROR, null, [
            'err_msg' => $errMsg,
        ]));
        return Response::output(static::formatErrMsg($errMsg), $statusCode);
    }

    public static function formatErrMsg($errMsg)
    {
        return call_user_func_array(config('error_handler.err_formatter'), [$errMsg]);
    }
}
