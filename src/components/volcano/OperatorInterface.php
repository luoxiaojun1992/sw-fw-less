<?php

namespace SwFwLess\components\volcano;

interface OperatorInterface
{
    public function open();

    public function next();

    public function close();
}
