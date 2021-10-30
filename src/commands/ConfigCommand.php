<?php

namespace SwFwLess\commands;

use SwFwLess\components\Config;
use Symfony\Component\Console\Input\InputOption;

class ConfigCommand extends AbstractCommand
{
    public $signature = 'config';

    protected function configure()
    {
        $this->addOption(
            'key',
            'key',
            InputOption::VALUE_OPTIONAL,
            'config key',
            null
        );
    }

    protected function handle()
    {
        $configKey = $this->input->getOption('key');
        return is_null($configKey) ? Config::all() : Config::get($configKey);
    }
}
