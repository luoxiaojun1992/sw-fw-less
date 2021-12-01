<?php

namespace SwFwLess\components\volcano\http\extractor;

use SwFwLess\components\volcano\AbstractOperator;

class ResponseExtractor extends AbstractOperator
{
    public function open()
    {
        //
    }

    public function next()
    {
        foreach ($this->nextOperator->next() as $response) {
            yield ((string)($response->getBody()));
        }
    }

    public function close()
    {
        //
    }
}
