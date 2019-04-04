<?php

namespace App\models;

use App\exceptions\ValidationException;
use App\models\traits\ModelArray;
use App\models\traits\ModelAttributes;
use App\models\traits\ModelEvents;
use App\models\traits\ModelJson;

abstract class AbstractModel implements \JsonSerializable, \ArrayAccess
{
    use ModelArray;
    use ModelAttributes;
    use ModelEvents;
    use ModelJson;

    protected static $primaryKey = 'id';
    protected static $bootedLock = [true];

    protected $newRecord = true;
    protected $justSaved = false;
    protected $needValidate = true;
    protected $returnErrors = true;
    protected $errors = [];
    protected $validationRules = [];
    protected $validationMessages = [];

    public function __construct()
    {
        $this->syncOriginalAttributes();

        static::bootOnce();
    }

    protected static function setFilter()
    {
        static::creating(function (self $model, $payload) {
            if (method_exists($model, 'beforeCreate')) {
                return call_user_func([$model, 'beforeCreate']);
            }
            return null;
        });
        static::created(function (self $model, $payload) {
            if (method_exists($model, 'afterCreate')) {
                return call_user_func([$model, 'afterCreate']);
            }
            return null;
        });
        static::updating(function (self $model, $payload) {
            if (method_exists($model, 'beforeUpdate')) {
                return call_user_func([$model, 'beforeUpdate']);
            }
            return null;
        });
        static::updated(function (self $model, $payload) {
            if (method_exists($model, 'afterUpdate')) {
                return call_user_func([$model, 'afterUpdate']);
            }
            return null;
        });
        static::deleting(function (self $model, $payload) {
            if (method_exists($model, 'beforeDelete')) {
                return call_user_func([$model, 'beforeDelete']);
            }
            return null;
        });
        static::deleted(function (self $model, $payload) {
            if (method_exists($model, 'afterDelete')) {
                return call_user_func([$model, 'afterDelete']);
            }
            return null;
        });
        static::saving(function (self $model, $payload) {
            if (method_exists($model, 'beforeSave')) {
                return call_user_func([$model, 'beforeSave']);
            }
            return null;
        });
        static::saved(function (self $model, $payload) {
            if (method_exists($model, 'afterSave')) {
                return call_user_func([$model, 'afterSave']);
            }
            return null;
        });
        static::validating(function (self $model, $payload) {
            if (method_exists($model, 'beforeValidate')) {
                return call_user_func([$model, 'beforeValidate']);
            }

            return null;
        });
        static::validated(function (self $model, $payload) {
            if (method_exists($model, 'afterValidate')) {
                return call_user_func([$model, 'afterValidate']);
            }
            return null;
        });
    }

    protected static function bootOnce()
    {
        if (array_pop(static::$bootedLock)) {
            static::setFilter();

            if (method_exists(static::class, 'boot')) {
                call_user_func([static::class, 'boot']);
            }
        }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param $primaryValue
     * @return AbstractModel
     */
    public function setPrimaryValue($primaryValue)
    {
        return $this->setAttribute(static::$primaryKey, $primaryValue);
    }

    /**
     * @return mixed|null
     */
    public function getPrimaryValue()
    {
        return $this->getAttribute(static::$primaryKey);
    }

    /**
     * @return bool
     */
    public function isNewRecord(): bool
    {
        return $this->newRecord;
    }

    /**
     * @param bool $newRecord
     * @return $this
     */
    public function setNewRecord(bool $newRecord)
    {
        $this->newRecord = $newRecord;
        return $this;
    }

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

    /**
     * @param bool $validate
     * @return bool
     */
    protected function beforeCreate($validate = false)
    {
        if ($validate) {
            return $this->validateWithEvents();
        }

        return null;
    }

    /**
     * @param bool $validate
     * @return bool
     */
    protected function beforeUpdate($validate = false)
    {
        if ($validate) {
            return $this->validateWithEvents();
        }

        return null;
    }

    protected function afterCreate()
    {
        $this->justSaved = true;

        if ($this->isNewRecord()) {
            $this->setNewRecord(false);
        }
    }

    protected function afterUpdate()
    {
        $this->justSaved = true;
    }

    protected function afterDelete()
    {
        if (!$this->isNewRecord()) {
            $this->setNewRecord(true);
        }
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

    protected function syncOriginalAttributes()
    {
        $this->originalAttributes = $this->attributes;
    }

    protected function finishSave()
    {
        $this->fireEvent('saved');
        $this->syncOriginalAttributes();
        $this->justSaved = false;
    }

    /**
     * @return bool
     */
    protected function isDirty()
    {
        if (!$this->isNewRecord()) {
            if ($this->justSaved) {
                return false;
            }

            foreach ($this->attributes as $key => $value) {
                if (array_key_exists($key, $this->originalAttributes)) {
                    if ($this->originalAttributes[$key] !== $value) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }
}
