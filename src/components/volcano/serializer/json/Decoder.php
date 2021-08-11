<?php

namespace SwFwLess\components\volcano\serializer\json;

use SwFwLess\components\Helper;
use SwFwLess\components\volcano\AbstractOperator;

class Decoder extends AbstractOperator
{
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
}
