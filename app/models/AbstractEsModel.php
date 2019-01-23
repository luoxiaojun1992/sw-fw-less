<?php

namespace App\models;

use App\components\es\BuilderFactory;
use App\components\es\ModelIndex;
use App\components\es\ModelQuery;

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
        $this->fireEvent('saving');

        $primaryKey = static::$primaryKey;

        $attributes = $this->toArray();

        if (count($attributes) > 0) {
            if (!$this->isNewRecord()) {
                $this->fireEvent('updating');
                if ($force || $this->isDirty()) {
                    $primaryValue = $this->getPrimaryValue();
                    if ($primaryValue) {
                        if (count($attributes) > 1) {
                            $attributes = $this->toArray();
                            $indexBuilder = static::index();
                            foreach ($attributes as $attributeName => $attribute) {
                                if ($attributeName == $primaryKey) {
                                    continue;
                                }

                                $indexBuilder->addField($attributeName, $attribute);
                            }
                            $res = $indexBuilder->id($primaryValue)->addDoc();
                            $result = $res['result'] == 'updated';
                            if ($result) {
                                $this->fireEvent('updated');
                                $this->finishSave();
                            }
                            return $result;
                        }
                    }
                }
            } else {
                $this->fireEvent('creating');
                $indexBuilder = static::index();
                foreach ($attributes as $attributeName => $attribute) {
                    if ($attributeName == $primaryKey) {
                        $indexBuilder->id($attribute);
                    }

                    $indexBuilder->addField($attributeName, $attribute);
                }
                $res = $indexBuilder->addDoc();

                if (!empty($res['_id'])) {
                    $this->setPrimaryValue($res['_id']);
                }

                $result = $res['result'] == 'created';
                if ($result) {
                    $this->fireEvent('created');
                    $this->finishSave();
                }
                return $result;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $this->fireEvent('deleting');

        if ($this->isNewRecord()) {
            return false;
        }

        $primaryValue = $this->getPrimaryValue();
        if ($primaryValue) {
            $res = static::index()->id($primaryValue)->deleteDoc();
            $result = $res['result'] == 'deleted';
            if ($result) {
                $this->fireEvent('deleted');
            }
            return $result;
        }

        return false;
    }
}
