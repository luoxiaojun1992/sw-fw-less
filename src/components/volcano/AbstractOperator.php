<?php

namespace SwFwLess\components\volcano;

abstract class AbstractOperator implements OperatorInterface
{
    /** @var AbstractOperator */
    protected $nextOperator;

    public function setNext(AbstractOperator $nextOperator)
    {
        $this->nextOperator = $nextOperator;
        return $this;
    }

    public function info()
    {
        return [];
    }
}
