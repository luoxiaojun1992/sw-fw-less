<?php

namespace SwFwLess\components\volcano\http\extractor;

use SwFwLess\components\volcano\OperatorInterface;

class ResponseExtractor implements OperatorInterface
{
    /** @var OperatorInterface */
    protected $nextOperator;

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function next()
    {
        foreach ($this->nextOperator->next() as $response) {
            yield ((string)($response->getBody()));
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
