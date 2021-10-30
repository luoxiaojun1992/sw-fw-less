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
        $config = is_null($configKey) ? Config::all() : Config::get($configKey);
        if (class_exists('Symfony\Component\VarDumper\VarDumper')) {
            \Symfony\Component\VarDumper\VarDumper::dump($config);
        } else {
            $this->output->writeln('<info>' . print_r($config, true) . '</info>');
        }
    }
}
