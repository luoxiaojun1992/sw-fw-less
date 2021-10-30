<?php

namespace SwFwLess\commands;

class ServerCommand extends AbstractCommand
{
    public $signature = 'server';

    protected function handle()
    {
        system('/usr/bin/env php ' . APP_BASE_PATH . 'start.php');
    }
}
