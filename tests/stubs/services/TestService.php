<?php

namespace SwFwLessTest\stubs\services;

use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;
use SwFwLess\services\BaseService;

class TestService extends BaseService
{
    public function test()
    {
        return [MetasyntacticVars::FOO => MetasyntacticVars::BAR];
    }
}
