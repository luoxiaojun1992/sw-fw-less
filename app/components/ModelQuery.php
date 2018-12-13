<?php

namespace App\components;

use App\models\AbstractMysqlModel;

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
     * @return AbstractMysqlModel
     */
    public function first($pdo = null)
    {
        $result = parent::first($pdo);
        $modelClass = $this->modelClass;
        return (new $modelClass)->setAttributes($result);
    }

    /**
     * @param null $pdo
     * @return AbstractMysqlModel[]|array
     */
    public function get($pdo = null)
    {
        $models = [];
        $results = parent::get($pdo);
        $modelClass = $this->modelClass;
        foreach ($results as $result) {
            $models[] = (new $modelClass)->setAttributes($result);
        }

        return $models;
    }
}
