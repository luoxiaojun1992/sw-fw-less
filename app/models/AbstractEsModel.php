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
     * @return bool
     */
    public function save()
    {
        $primaryKey = static::$primaryKey;

        $attributes = $this->toArray();

        if (count($attributes) > 0) {
            $primaryValue = $this->{$primaryKey};
            if ($primaryValue) {
                if (count($attributes) > 1) {
                    $indexBuilder = static::index();
                    foreach ($attributes as $attributeName => $attribute) {
                        $indexBuilder->addField($attributeName, $attribute);
                    }
                    $res = $indexBuilder->id($primaryValue)->addDoc();
                    return $res['result'] == 'updated';
                }
            } else {
                $indexBuilder = static::index();
                foreach ($attributes as $attributeName => $attribute) {
                    $indexBuilder->addField($attributeName, $attribute);
                }
                $res = $indexBuilder->addDoc();

                if (!empty($res['_id'])) {
                    $this->setPrimaryValue($res['_id']);
                }

                return $res['result'] == 'created';
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $primaryKey = static::$primaryKey;
        $primaryValue = $this->{$primaryKey};
        if ($primaryValue) {
            $res = static::index()->id($primaryValue)->deleteDoc();
            return $res['result'] == 'deleted';
        }

        return false;
    }
}
