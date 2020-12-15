<?php

class Query extends \SwFwLess\components\mysql\Query
{
    public static function tablePrefix($db, $connectionName): string
    {
        return 'test_';
    }

    public static function connectionName($db, $connectionName = null): string
    {
        return 'test';
    }
}
