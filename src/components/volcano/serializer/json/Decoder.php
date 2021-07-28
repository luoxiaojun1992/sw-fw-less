<?php

namespace SwFwLess\components\volcano\serializer\json;

use SwFwLess\components\volcano\OperatorInterface;

class Decoder implements OperatorInterface
{
    /** @var OperatorInterface */
    protected $nextOperator;

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function next()
    {
        // TODO: Implement next() method.
        foreach ($this->nextOperator->next() as $response) {
            $data = [];
            yield $data;
        }
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function setNext(OperatorInterface $nextOperator)
    {
        $this->nextOperator = $nextOperator;
        return $this;
    }
}
