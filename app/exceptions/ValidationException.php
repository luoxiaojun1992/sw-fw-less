<?php

namespace App\exceptions;

use App\components\Helper;

class ValidationException extends HttpException
{
    private $errors = [];

    public function __construct($errors = [], int $code = 400, \Throwable $previous = null)
    {
        parent::__construct(Helper::jsonEncode($this->errors = $errors), $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->message = Helper::jsonEncode($this->errors = $errors);
        return $this;
    }
}
