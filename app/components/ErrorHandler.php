<?php

namespace App\components;

class ErrorHandler
{
    public static function handle(\Exception $e)
    {
        $statusCode = !is_string($e->getCode()) && $e->getCode() ? $e->getCode() : 500;
        return Response::output(nl2br($e->getMessage() . PHP_EOL . $e->getTraceAsString()), $statusCode);
    }
}
