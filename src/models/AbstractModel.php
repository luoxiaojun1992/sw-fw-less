<?php

namespace SwFwLess\models;

use SwFwLess\components\container\Container;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\models\traits\ModelAttributes;
use SwFwLess\models\traits\ModelEvents;

abstract class AbstractModel extends Container
{
    use ModelAttributes;
    use ModelEvents;

    protected static $primaryKey = 'id';
    protected static $incrPrimaryKey = true;
    protected static $bootedLock = [true];

    protected $newRecord = true;
    protected $justSaved = false;

    public function __construct()
    {
        $this->syncOriginalAttributes();

        static::bootOnce();
    }

    protected static function bootedLock()
    {
        return Scheduler::withoutPreemptive(function () {
            return array_pop(static::$bootedLock);
        });
    }

    protected static function bootOnce()
    {
        if (static::bootedLock()) {
            static::setFilter();
        }

        if (method_exists(static::class, 'boot')) {
            call_user_func([static::class, 'boot']);
        }
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

            foreach ($this->data as $key => $value) {
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
