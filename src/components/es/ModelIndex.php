<?php

namespace SwFwLess\components\es;

use SwFwLess\models\AbstractEsModel;
use Lxj\Laravel\Elasticsearch\Builder\IndexBuilder;

class ModelIndex extends IndexBuilder
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
}
