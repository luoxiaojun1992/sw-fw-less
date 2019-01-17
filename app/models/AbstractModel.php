<?php

namespace App\models;

use App\components\Helper;
use App\models\traits\ModelArrayTrait;
use App\models\traits\ModelEventsTrait;
use App\models\traits\ModelJsonTrait;
use Cake\Event\Event;

abstract class AbstractModel implements \JsonSerializable, \ArrayAccess
{
    use ModelArrayTrait;
    use ModelJsonTrait;
    use ModelEventsTrait;

    protected static $primaryKey = 'id';
    protected static $bootedLock = [true];

    private $attributes = [];

    public function __construct()
    {
        static::bootOnce();
    }

    protected static function setFilter()
    {
        static::creating(function (Event $event) {
            $model = $event->getData('model');
            if (method_exists($model, 'beforeCreate')) {
                call_user_func([$model, 'beforeCreate']);
            }
        });
        static::created(function (Event $event) {
            $model = $event->getData('model');
            if (method_exists($model, 'afterCreate')) {
                call_user_func([$model, 'afterCreate']);
            }
        });
        static::updating(function (Event $event) {
            $model = $event->getData('model');
            if (method_exists($model, 'beforeUpdate')) {
                call_user_func([$model, 'beforeUpdate']);
            }
        });
        static::updated(function (Event $event) {
            $model = $event->getData('model');
            if (method_exists($model, 'afterUpdate')) {
                call_user_func([$model, 'afterUpdate']);
            }
        });
        static::saving(function (Event $event) {
            $model = $event->getData('model');
            if (method_exists($model, 'beforeSave')) {
                call_user_func([$model, 'beforeSave']);
            }
        });
        static::saved(function (Event $event) {
            $model = $event->getData('model');
            if (method_exists($model, 'afterSave')) {
                call_user_func([$model, 'afterSave']);
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
}
