<?php

namespace SwFwLess\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCommand extends Command
{
    public $signature;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function __construct(string $name = null)
    {
        parent::__construct($this->signature);

    }

    protected function handle()
    {
        //
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->handle();

        return 1;
    }
}
