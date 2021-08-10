<?php

namespace SwFwLess\models;

use SwFwLess\components\es\BuilderFactory;
use SwFwLess\components\es\ModelIndex;
use SwFwLess\components\es\ModelQuery;

abstract class AbstractEsModel extends AbstractModel
{
    protected static $connection = 'default';
    protected static $index = '';
    protected static $type = '';

    /**
     * @return ModelQuery|\Lxj\Laravel\Elasticsearch\Builder\QueryBuilder
     */
    public static function query()
    {
        return BuilderFactory::createQueryBuilderFromConnection(ModelQuery::class, static::$connection)
            ->setModelClass(static::class)
            ->index(static::$index)
            ->type(static::$type);
    }

    /**
     * @return ModelIndex|\Lxj\Laravel\Elasticsearch\Builder\IndexBuilder
     */
    public static function index()
    {
        return BuilderFactory::createIndexBuilderFromConnection(ModelIndex::class, static::$connection)
            ->setModelClass(static::class)
            ->index(static::$index)
            ->type(static::$type);
    }

    /**
     * @param bool $force
     * @return bool
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

        $primaryKey = static::$primaryKey;
        $indexBuilder = static::index();
        foreach ($this->data as $attributeName => $attributeValue) {
            if ($attributeName == $primaryKey) {
                $indexBuilder->id($attributeValue);
            }

            $indexBuilder->addField($attributeName, $attributeValue);
        }
        $res = $indexBuilder->addDoc();

        if ($result = ($res['result'] === 'created')) {
            $this->setPrimaryValue($res['_id']);
            $this->fireEvent('created');
        }

        return $result;
    }

    protected function performUpdate($force = false)
    {
        if ($this->fireEvent('updating')->isStopped()) {
            return false;
        }

        if (!$force && !$this->isDirty()) {
            return false;
        }

        $primaryKey = static::$primaryKey;
        $indexBuilder = static::index();
        foreach ($this->data as $attributeName => $attributeValue) {
            if ($attributeName == $primaryKey) {
                continue;
            }

            $indexBuilder->addField($attributeName, $attributeValue);
        }
        $res = $indexBuilder->id($this->getPrimaryValue())->addDoc();
        if ($result = ($res['result'] === 'updated')) {
            $this->fireEvent('updated');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        if ($this->fireEvent('deleting')->isStopped()) {
            return false;
        }

        if ($this->isNewRecord()) {
            return false;
        }

        $res = static::index()->id($this->getPrimaryValue())->deleteDoc();
        if ($result = ($res['result'] === 'deleted')) {
            $this->fireEvent('deleted');
        }
        return $result;
    }
}
