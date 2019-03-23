<?php

namespace App\models\traits;

use App\facades\Event;
use Cake\Event\Event as CakeEvent;

trait ModelEvents
{
    /**
     * Fire a model event
     *
     * @param string $event
     * @param mixed $payload
     * @return CakeEvent
     */
    protected function fireEvent($event, $payload = null)
    {
        return event(
            new CakeEvent(
                'model.' . static::class . '.' . $event,
                null,
                ['model' => $this, 'payload' => $payload]
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
            'model.' . static::class . '.' . $event,
            [],
            function(CakeEvent $event) use ($callback) {
                call_user_func_array($callback, $event->getData());
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
}
