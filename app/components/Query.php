<?php

namespace App\components;

use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;

class Query
{
    /** @var QueryInterface|QueryFactory */
    private $auraQuery;

    /**
     * @param $db
     * @return QueryFactory|Query
     */
    public static function create($db)
    {
        return new self($db);
    }

    /**
     * @return QueryFactory|Query
     */
    public static function createMysql()
    {
        return self::create('mysql');
    }

    public function __construct($db)
    {
        $this->auraQuery = new QueryFactory($db);
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->auraQuery, $name], $arguments);
        if (is_object($result)) {
            $this->auraQuery = $result;
        }
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function execute()
    {
        try {
            $pdo = Mysql::create()->pick();

            $pdoStatement = $pdo->prepare($this->auraQuery->getStatement());
            if ($pdoStatement) {
                if ($pdoStatement->execute($this->auraQuery->getBindValues())) {
                    return $pdoStatement->fetch(\PDO::FETCH_ASSOC);
                }
            }
        } catch (\PDOException $e) {
            //todo handle exception
        }

        return [];
    }
}
