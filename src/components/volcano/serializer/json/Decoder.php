<?php

namespace SwFwLess\components\volcano\serializer\json;

use SwFwLess\components\Helper;
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
        foreach ($this->nextOperator->next() as $str) {
            yield Helper::jsonDecode($str);
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
