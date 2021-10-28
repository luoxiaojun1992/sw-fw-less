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

        $this->symfonyApplication = new Application();
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

        $this->symfonyApplication->addCommands(
            [
                new TinkerCommand(),
            ]
        );
    }

    public function run()
    {
        echo 'Developing...', PHP_EOL;

        $this->symfonyApplication->run();
    }
}
