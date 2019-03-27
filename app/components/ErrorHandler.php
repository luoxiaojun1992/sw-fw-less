<?php

namespace App\components;

use App\components\http\Response;
use App\exceptions\HttpException;

class ErrorHandler
{
    public static function handle(\Throwable $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getCode();
        } else {
            $statusCode = 500;
        }
        $errMsg = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        \App\facades\Log::err($errMsg);
        return Response::output(static::formatErrMsg($errMsg), $statusCode);
    }

    public static function formatErrMsg($errMsg)
    {
        return nl2br($errMsg);
    }
}
