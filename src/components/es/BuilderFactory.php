<?php

namespace SwFwLess\components\es;

use SwFwLess\facades\Es;
use Lxj\Laravel\Elasticsearch\Builder\IndexBuilder;
use Lxj\Laravel\Elasticsearch\Builder\QueryBuilder;

class BuilderFactory
{
    /**
     * @param null $builderClass
     * @return QueryBuilder|ModelQuery
     */
    public static function createQueryBuilder($builderClass = null)
    {
        return self::createQueryBuilderFromConnection($builderClass);
    }

    /**
     * @param null $builderClass
     * @return IndexBuilder|ModelIndex
     */
    public static function createIndexBuilder($builderClass = null)
    {
        return self::createIndexBuilderFromConnection($builderClass);
    }

    /**
     * @param null $builderClass
     * @param string $connection
     * @return QueryBuilder|ModelQuery
     */
    public static function createQueryBuilderFromConnection($builderClass = null, $connection = 'default')
    {
        if (!$builderClass) {
            $builderClass = QueryBuilder::class;
        }
        return new $builderClass(self::createConnection($connection));
    }

    /**
     * @param null $builderClass
     * @param string $connection
     * @return IndexBuilder|ModelIndex
     */
    public static function createIndexBuilderFromConnection($builderClass = null, $connection = 'default')
    {
        if (!$builderClass) {
            $builderClass = IndexBuilder::class;
        }
        return new $builderClass(self::createConnection($connection));
    }

    /**
     * @param string $connection
     * @return \Elasticsearch\Client|null
     */
    public static function createConnection($connection = 'default')
    {
        return Es::connection($connection);
    }
}
