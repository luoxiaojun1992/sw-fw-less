<?php

namespace SwFwLess\components\volcano;

abstract class AbstractOperator implements OperatorInterface
{
    /** @var AbstractOperator */
    protected $nextOperator;

    public function open()
    {
        //
    }

    public function close()
    {
        //
    }

    /**
     * @return \Generator
     */
    public function next()
    {
        if (!is_null($this->nextOperator)) {
            return $this->nextOperator->next();
        }
    }

    public function setNext(AbstractOperator $nextOperator)
    {
        $this->nextOperator = $nextOperator;
        return $this;
    }

    public function info()
    {
        return [];
    }

    public static function create($info = [])
    {
        return new static();
    }
}
