<?php

namespace SwFwLess\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TinkerCommand extends AbstractCommand
{
    public $signature = 'tinker';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $_SERVER['argv'] = [];
        $_SERVER['argc'] = 0;
        $argv = [];
        $argc = 0;

        // And go!
        call_user_func(\Psy\bin());
    }
}