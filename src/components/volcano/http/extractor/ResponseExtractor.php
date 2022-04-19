<?php

namespace SwFwLess\components\volcano\http\extractor;

use SwFwLess\components\volcano\AbstractOperator;

class ResponseExtractor extends AbstractOperator
{
    public function next()
    {
        foreach (parent::next() as $response) {
            yield ((string)($response->getBody()));
        }
    }
}
