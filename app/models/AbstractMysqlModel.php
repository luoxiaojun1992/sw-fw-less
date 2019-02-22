<?php

namespace App\models;

use App\components\ModelQuery;
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryInterface;

abstract class AbstractMysqlModel extends AbstractModel
{
    protected static $table = '';

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function select()
    {
        return ModelQuery::select()->from(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function update()
    {
        return ModelQuery::update()->table(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function insert()
    {
        return ModelQuery::insert()->into(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface|InsertInterface|DeleteInterface|UpdateInterface
     */
    public static function delete()
    {
        return ModelQuery::delete()->from(static::$table)->setModelClass(static::class);
    }

    /**
     * @param bool $force
     * @return bool|mixed
     */
    public function save($force = false)
    {
        if ($this->fireEvent('saving')->getResult() === false) {
            return false;
        }

        if ($result = ($this->isNewRecord() ? $this->performInsert() : $this->performUpdate($force))) {
            $this->finishSave();
        }

        return $result;
    }

    protected function performInsert()
    {
        if ($this->fireEvent('creating')->getResult() === false) {
            return false;
        }

        $insertBuilder = static::insert();
        foreach ($this->attributes as $attributeName => $attribute) {
            $insertBuilder->col($attributeName)->bindValue($attributeName, $this->{$attributeName});
        }

        $res = $insertBuilder->write() > 0;

        $lastInsetId = $insertBuilder->getLastInsertId();
        if ($lastInsetId) {
            $this->setPrimaryValue($lastInsetId);
        }

        if ($res) {
            $this->fireEvent('created');
        }

        return $res;
    }

    protected function performUpdate($force = false)
    {
        if ($this->fireEvent('updating')->getResult() === false) {
            return false;
        }

        if (!$force && !$this->isDirty()) {
            return false;
        }

        $attributes = $this->attributes;

        if (count($attributes) < 1) {
            return false;
        }

        $attributes = $this->toArray();
        $updateBuilder = static::update();
        $primaryKey = static::$primaryKey;
        $updateBuilder->where("`{$primaryKey}` = :primaryValue", ['primaryValue' => $this->getPrimaryValue()]);
        foreach ($attributes as $attributeName => $attribute) {
            if ($attributeName == $primaryKey) {
                continue;
            }

            $updateBuilder->col($attributeName)->bindValue($attributeName, $this->{$attributeName});
        }
        $updateBuilder->write();
        $this->fireEvent('updated');

        return true;
    }

    /**
     * @return bool
     */
    public function del()
    {
        if ($this->fireEvent('deleting')->getResult() === false) {
            return false;
        }

        if ($this->isNewRecord()) {
            return false;
        }

        $primaryKey = static::$primaryKey;
        static::delete()->where("`{$primaryKey}` = :primaryValue", ['primaryValue' => $this->getPrimaryValue()])
            ->write();

        $this->fireEvent('deleted');
        return true;
    }
}
