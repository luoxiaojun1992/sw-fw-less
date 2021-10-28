<?php

namespace SwFwLess\bootstrap;

use SwFwLess\commands\TinkerCommand;
use SwFwLess\components\provider\KernelProvider;
use Symfony\Component\Console\Application;

class Command extends Kernel
{
    /**
     * @var Application
     */
    protected $symfonyApplication;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param bool $reboot
     * @throws \Exception
     */
    protected function bootstrap($reboot = false)
    {
        $this->setSapi(php_sapi_name());

        parent::bootstrap($reboot);

        KernelProvider::bootCommand();

        $this->symfonyApplication = new Application();
        $this->symfonyApplication->addCommands(
            [
                new TinkerCommand(),
            ]
        );
    }

    public function run()
    {
        $this->symfonyApplication->run();
    }
}
