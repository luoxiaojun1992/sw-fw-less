<?php

namespace SwFwLess\models;

use SwFwLess\components\mysql\ModelQuery;
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryInterface;
use SwFwLess\components\mysql\Query;

abstract class AbstractMysqlModel extends AbstractModel
{
    protected static $table = '';

    protected static $connectionName = null;

    public static function connectionName(): string
    {
        return Query::connectionName('mysql', static::$connectionName);
    }

    public static function tablePrefix(): string
    {
        return Query::tablePrefix('mysql', static::connectionName());
    }

    public static function tableName(): string
    {
        return (static::tablePrefix()) . (static::$table);
    }

    /**
     * @return ModelQuery|QueryInterface|SelectInterface
     */
    public static function select()
    {
        return ModelQuery::select('mysql', static::$connectionName)->fromWithPrefix(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|UpdateInterface
     */
    public static function update()
    {
        return ModelQuery::update('mysql', static::$connectionName)->tableWithPrefix(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|InsertInterface
     */
    public static function insert()
    {
        return ModelQuery::insert('mysql', static::$connectionName)->intoWithPrefix(static::$table)->setModelClass(static::class);
    }

    /**
     * @return ModelQuery|QueryInterface|DeleteInterface
     */
    public static function delete()
    {
        return ModelQuery::delete('mysql', static::$connectionName)->fromWithPrefix(static::$table)->setModelClass(static::class);
    }

    /**
     * @param bool $force
     * @return bool|mixed
     */
    public function save($force = false)
    {
        if ($this->fireEvent('saving')->isStopped()) {
            return false;
        }

        if ($result = ($this->isNewRecord() ? $this->performInsert() : $this->performUpdate($force))) {
            $this->finishSave();
        }

        return $result;
    }

    protected function performInsert()
    {
        if ($this->fireEvent('creating')->isStopped()) {
            return false;
        }

        $insertBuilder = static::insert();
        foreach ($this->attributes as $attributeName => $attribute) {
            $insertBuilder->col($attributeName)->bindValue(':' . $attributeName, $this->{$attributeName});
        }
        if (static::$incrPrimaryKey) {
            $insertBuilder->setSequence(static::$primaryKey)
                ->setHasSequence(true);
        } else {
            $insertBuilder->setHasSequence(false);
        }

        $res = $insertBuilder->write() > 0;

        if (static::$incrPrimaryKey) {
            $lastInsetId = $insertBuilder->getLastInsertId();
            if ($lastInsetId) {
                $this->setPrimaryValue($lastInsetId);
            }
        }

        if ($res) {
            $this->fireEvent('created');
        }

        return $res;
    }

    protected function performUpdate($force = false)
    {
        if ($this->fireEvent('updating')->isStopped()) {
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
        $updateBuilder->where("`{$primaryKey}` = :primaryValue");
        $updateBuilder->bindValue(':primaryValue', $this->getPrimaryValue());
        foreach ($attributes as $attributeName => $attribute) {
            if ($attributeName == $primaryKey) {
                continue;
            }

            $updateBuilder->col($attributeName)->bindValue(':' . $attributeName, $this->{$attributeName});
        }
        $updateBuilder->setHasSequence(false);
        $updateBuilder->write();
        $this->fireEvent('updated');

        return true;
    }

    /**
     * @return bool
     */
    public function del()
    {
        if ($this->fireEvent('deleting')->isStopped()) {
            return false;
        }

        if ($this->isNewRecord()) {
            return false;
        }

        $primaryKey = static::$primaryKey;
        static::delete()->where("`{$primaryKey}` = :primaryValue")
            ->bindValue(':primaryValue', $this->getPrimaryValue())
            ->write();

        $this->fireEvent('deleted');
        return true;
    }
}
