<?php

namespace SwFwLess\exceptions;

class HttpException extends \RuntimeException
{
    public function __construct($message = '', int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
