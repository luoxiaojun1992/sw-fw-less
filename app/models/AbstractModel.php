<?php

namespace App\models;

use App\components\Helper;
use App\exceptions\ValidationException;
use App\models\traits\ModelArrayTrait;
use App\models\traits\ModelEventsTrait;
use App\models\traits\ModelJsonTrait;

abstract class AbstractModel implements \JsonSerializable, \ArrayAccess
{
    use ModelArrayTrait;
    use ModelJsonTrait;
    use ModelEventsTrait;

    protected static $primaryKey = 'id';
    protected static $bootedLock = [true];

    private $originalAttributes = [];
    private $attributes = [];
    private $newRecord = true;

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
     * @param $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    public function setAttribute($name, $value)
    {
        $setter = 'set' . Helper::snake2Camel($name);
        if (method_exists($this, $setter)) {
            call_user_func_array([$this, $setter], [$value]);
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }

    public function getAttribute($name)
    {
        if ($this->attributeExists($name)) {
            $getter = 'get' . Helper::snake2Camel($name);
            if (method_exists($this, $getter)) {
                return call_user_func([$this, $getter]);
            } else {
                return $this->attributes[$name];
            }
        }

        return null;
    }

    public function removeAttribute($name)
    {
        $setter = 'remove' . Helper::snake2Camel($name);
        if (method_exists($this, $setter)) {
            call_user_func([$this, $setter]);
        } else {
            unset($this->attributes[$name]);
        }
    }

    public function attributeExists($name)
    {
        $getter = Helper::snake2Hump($name) . 'Exists';
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        } else {
            return array_key_exists($name, $this->toArray());
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
        if ($this->isNewRecord()) {
            $this->setNewRecord(false);
        }
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
    }
}
