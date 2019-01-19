<?php

namespace App\components\es;

use App\models\AbstractEsModel;
use Lxj\Laravel\Elasticsearch\Builder\QueryBuilder;

class ModelQuery extends QueryBuilder
{
    /** @var AbstractEsModel */
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
     * @return AbstractEsModel[]|array
     */
    public function search()
    {
        $models = [];
        $result = parent::search();
        if ($result['hits']['total'] > 0) {
            $models = $this->setModels($result['hits']['hits']);
        }

        return $models;
    }

    /**
     * @return AbstractEsModel[]|array
     */
    public function get()
    {
        $models = [];
        $result = parent::get();
        if ($result['found']) {
            $models = $this->setModels([$result]);
        }

        return count($models) > 0 ? $models[0] : null;
    }

    /**
     * @param $docs
     * @return AbstractEsModel[]|array
     */
    private function setModels($docs)
    {
        $models = [];
        $modelClass = $this->modelClass;
        foreach ($docs as $doc) {
            $models[] = (new $modelClass)->setAttributes($doc['_source'])
                ->setPrimaryValue($doc['_id'])
                ->setNewRecord(false);
        }

        return $models;
    }
}
