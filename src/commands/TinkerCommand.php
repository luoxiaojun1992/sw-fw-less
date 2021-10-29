<?php

namespace SwFwLess\commands;

class TinkerCommand extends AbstractCommand
{
    public $signature = 'tinker';

    protected function handle()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argc'] = 0;
        $argv = [];
        $argc = 0;

        // And go!
        call_user_func(\Psy\bin());
    }
}
