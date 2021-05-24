<?php

namespace SwFwLess\components\mysql;

use SwFwLess\models\AbstractMysqlModel;

class ModelQuery extends Query
{
    /** @var AbstractMysqlModel */
    public $modelClass;

    /**
     * @param $modelClass
     * @return $this
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * @param null $pdo
     * @param bool|null $retry
     * @param string|null $sql
     * @param array|null $bindValues
     * @return AbstractMysqlModel|null
     * @throws \Exception
     */
    public function first($pdo = null, $retry = null, $sql = null, $bindValues = null)
    {
        $result = parent::first($pdo, $retry, $sql, $bindValues);
        $modelClass = $this->modelClass;
        return $result ? (new $modelClass)->setAttributes($result)->setNewRecord(false) : null;
    }

    /**
     * @param null $pdo
     * @param bool|null $retry
     * @param string|null $sql
     * @param array|null $bindValues
     * @return AbstractMysqlModel[]|array
     * @throws \Exception
     */
    public function get($pdo = null, $retry = null, $sql = null, $bindValues = null)
    {
        $models = [];
        $results = parent::get($pdo, $retry, $sql, $bindValues);
        $modelClass = $this->modelClass;
        foreach ($results as $result) {
            $models[] = (new $modelClass)->setAttributes($result)->setNewRecord(false);
        }

        return $models;
    }
}
