<?php

namespace App\components\es;

use Lxj\Laravel\Elasticsearch\Builder\IndexBuilder;
use Lxj\Laravel\Elasticsearch\Builder\QueryBuilder;

class BuilderFactory
{
    public static function createQueryBuilder()
    {
        return self::createQueryBuilderFromConnection();
    }

    public static function createIndexBuilder()
    {
        return self::createIndexBuilderFromConnection();
    }

    public static function createQueryBuilderFromConnection($connection = 'default')
    {
        return new QueryBuilder(self::createConnection($connection));
    }

    public static function createIndexBuilderFromConnection($connection = 'default')
    {
        return new IndexBuilder(self::createConnection($connection));
    }

    public static function createConnection($connection = 'default')
    {
        //todo replace with facade
        return Manager::create()->connection($connection);
    }
}
