<?php

namespace App\exceptions;

use App\components\Helper;
use Throwable;

class ValidationException extends \RuntimeException
{
    private $errors = [];

    public function __construct(array $errors = [], int $code = 0, Throwable $previous = null)
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
