<?php

namespace App\services;

use App\components\Mysql;
use App\components\Redis;
use App\components\Response;
use Aura\SqlQuery\QueryFactory;

class DemoService extends BaseService
{
    public function foo()
    {
        $redisPool = Redis::create();
        $redis = $redisPool->pick();
        $result = $redis->get('key');
        $redisPool->release($redis);

        if (!$result) {
            $mysqlPool = Mysql::create();
            $pdo = $mysqlPool->pick();

            $query = (new QueryFactory('mysql'))->newSelect()->from('test')->cols(['id'])->limit(1);
            $pdoStatement = $pdo->prepare($query->getStatement());
            if ($pdoStatement) {
                if ($pdoStatement->execute($query->getBindValues())) {
                    $queryResult = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
                }
            }
        }

        return Response::output($result);
    }
}
