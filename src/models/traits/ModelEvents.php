<?php

namespace SwFwLess\models\traits;

use SwFwLess\facades\Event;
use Cake\Event\Event as CakeEvent;
use SwFwLess\models\Definitions;

trait ModelEvents
{
    use ModelValidator;

    /**
     * Fire a model event
     *
     * @param string $event
     * @param mixed $payload
     * @return \Cake\Event\Event
     */
    protected function fireEvent(string $event, $payload = null)
    {
        return \SwFwLess\components\functions\event(
            new CakeEvent(
                Definitions::MODEL_EVENT,
                null,
                [$event, ['model' => $this, 'payload' => $payload]]
            )
        );
    }

    /**
     * Listen a model event
     *
     * @param $event
     * @param $callback
     */
    public static function listenEvent($event, $callback)
    {
        Event::on(
            Definitions::MODEL_EVENT,
            [],
            function(CakeEvent $event) use ($event, $callback) {
                list($eventName, $eventData) = $event->getData();
                if ($eventName === $event) {
                    call_user_func_array($callback, $eventData);
                }
            }
        );
    }

    /**
     * Listen a model creating event
     *
     * @param $callback
     */
    public static function creating($callback)
    {
        self::listenEvent('creating', $callback);
    }

    /**
     * Listen a model created event
     *
     * @param $callback
     */
    public static function created($callback)
    {
        self::listenEvent('created', $callback);
    }

    /**
     * Listen a model updating event
     *
     * @param $callback
     */
    public static function updating($callback)
    {
        self::listenEvent('updating', $callback);
    }

    /**
     * Listen a model updated event
     *
     * @param $callback
     */
    public static function updated($callback)
    {
        self::listenEvent('updated', $callback);
    }

    /**
     * Listen a model deleting event
     *
     * @param $callback
     */
    public static function deleting($callback)
    {
        self::listenEvent('deleting', $callback);
    }

    /**
     * Listen a model deleted event
     *
     * @param $callback
     */
    public static function deleted($callback)
    {
        self::listenEvent('deleted', $callback);
    }

    /**
     * Listen a model saving event
     *
     * @param $callback
     */
    public static function saving($callback)
    {
        self::listenEvent('saving', $callback);
    }

    /**
     * Listen a model saved event
     *
     * @param $callback
     */
    public static function saved($callback)
    {
        self::listenEvent('saved', $callback);
    }

    /**
     * Listen a model validating event
     *
     * @param $callback
     */
    public static function validating($callback)
    {
        self::listenEvent('validating', $callback);
    }

    /**
     * Listen a model validated event
     *
     * @param $callback
     */
    public static function validated($callback)
    {
        self::listenEvent('validated', $callback);
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
}
