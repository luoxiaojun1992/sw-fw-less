<?php

namespace SwFwLess\commands;

class ServerCommand extends AbstractCommand
{
    public $signature = 'server';

    protected function handle()
    {
        shell_exec(APP_BASE_PATH . 'start.php');
    }
}
