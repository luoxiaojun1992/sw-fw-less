<?php

namespace SwFwLess\models\traits;

use SwFwLess\exceptions\ValidationException;

trait ModelValidator
{
    protected $needValidate = true;
    protected $returnErrors = true;
    protected $errors = [];

    /**
     * @return bool
     */
    public function isNeedValidate(): bool
    {
        return $this->needValidate;
    }

    /**
     * @param bool $needValidate
     * @return $this
     */
    public function setNeedValidate(bool $needValidate)
    {
        $this->needValidate = $needValidate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReturnErrors(): bool
    {
        return $this->returnErrors;
    }

    /**
     * @param bool $returnErrors
     * @return $this
     */
    public function setReturnErrors(bool $returnErrors)
    {
        $this->returnErrors = $returnErrors;
        return $this;
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
        $this->errors = $errors;
        return $this;
    }

    protected function validateWithEvents()
    {
        if ($this->fireEvent('validating')->isStopped()) {
            $this->setErrors(['Error before validation']);
            if ($this->isReturnErrors()) {
                return false;
            } else {
                throw new ValidationException(['Error before validation'], 400);
            }
        }

        if ($this->isNeedValidate()) {
            if (count($errors = $this->validate()) > 0) {
                $this->setErrors($errors);
                if ($this->isReturnErrors()) {
                    return false;
                } else {
                    throw new ValidationException($errors, 400);
                }
            }
        }

        $this->fireEvent('validated');

        return true;
    }

    /**
     * @return array $errors
     */
    protected function validate() : array
    {
        return [];
    }
}
