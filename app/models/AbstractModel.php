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

    public function __construct()
    {
        $this->syncOriginalAttributes();

        static::bootOnce();
    }

    protected static function setFilter()
    {
        static::creating(function (self $model, $payload) {
            if (method_exists($model, 'beforeCreate')) {
                call_user_func([$model, 'beforeCreate']);
            }
        });
        static::created(function (self $model, $payload) {
            if (method_exists($model, 'afterCreate')) {
                call_user_func([$model, 'afterCreate']);
            }
        });
        static::updating(function (self $model, $payload) {
            if (method_exists($model, 'beforeUpdate')) {
                call_user_func([$model, 'beforeUpdate']);
            }
        });
        static::updated(function (self $model, $payload) {
            if (method_exists($model, 'afterUpdate')) {
                call_user_func([$model, 'afterUpdate']);
            }
        });
        static::deleting(function (self $model, $payload) {
            if (method_exists($model, 'beforeDelete')) {
                call_user_func([$model, 'beforeDelete']);
            }
        });
        static::deleted(function (self $model, $payload) {
            if (method_exists($model, 'afterDelete')) {
                call_user_func([$model, 'afterDelete']);
            }
        });
        static::saving(function (self $model, $payload) {
            if (method_exists($model, 'beforeSave')) {
                call_user_func([$model, 'beforeSave']);
            }
        });
        static::saved(function (self $model, $payload) {
            if (method_exists($model, 'afterSave')) {
                call_user_func([$model, 'afterSave']);
            }
        });
        static::validating(function (self $model, $payload) {
            if (method_exists($model, 'beforeValidate')) {
                call_user_func([$model, 'beforeValidate']);
            }
        });
        static::validated(function (self $model, $payload) {
            if (method_exists($model, 'afterValidate')) {
                call_user_func([$model, 'afterValidate']);
            }
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
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
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
     * @param bool $validate
     */
    protected function beforeCreate($validate = false)
    {
        if ($validate) {
            $this->__validate();
        }
    }

    /**
     * @param bool $validate
     */
    protected function beforeUpdate($validate = false)
    {
        if ($validate) {
            $this->__validate();
        }
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

    private function __validate()
    {
        $this->fireEvent('validating');

        if (count($errors = $this->validate()) > 0) {
            throw new ValidationException($errors, 400);
        }

        $this->fireEvent('validated');
    }

    /**
     * @return array
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
                    return $this->originalAttributes[$key] != $value;
                } else {
                    return true;
                }
            }
        }

        return false;
    }
}
