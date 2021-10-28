<?php

namespace SwFwLess\commands;

use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    public $signature;

    public function __construct(string $name = null)
    {
        parent::__construct($this->signature);
    }
}
