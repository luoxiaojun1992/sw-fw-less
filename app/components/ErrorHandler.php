<?php

namespace App\components;

use App\components\http\Response;

class ErrorHandler
{
    public static function handle(\Exception $e)
    {
        $statusCode = !is_string($e->getCode()) && $e->getCode() ? $e->getCode() : 500;
        $errMsg = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        \App\facades\Log::err($errMsg);
        return Response::output($errMsg, $statusCode);
    }
}
