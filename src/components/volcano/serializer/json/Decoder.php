<?php

namespace SwFwLess\components\volcano\serializer\json;

use SwFwLess\components\Helper;
use SwFwLess\components\volcano\AbstractOperator;

class Decoder extends AbstractOperator
{
    public function next()
    {
        foreach (parent::next() as $str) {
            yield Helper::jsonDecode($str);
        }
    }
}
