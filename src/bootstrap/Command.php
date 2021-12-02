<?php

namespace SwFwLess\bootstrap;

use SwFwLess\commands\ConfigCommand;
use SwFwLess\commands\CsvExtractorCommand;
use SwFwLess\commands\generators\ModelGenerator;
use SwFwLess\commands\generators\ServiceGenerator;
use SwFwLess\commands\ServerCommand;
use SwFwLess\commands\TinkerCommand;
use SwFwLess\components\Config;
use SwFwLess\components\provider\KernelProvider;
use SwFwLess\components\utils\FilesystemUtil;
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

        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->registerKernelCommands();
        $this->registerCustomCommands();
        $this->registerConfiguredCommands();
    }

    protected function registerKernelCommands()
    {
        $this->symfonyApplication->addCommands(
            [
                new TinkerCommand(),
                new ServerCommand(),
                new ConfigCommand(),
                new CsvExtractorCommand(),
                new ModelGenerator(),
                new ServiceGenerator(),
            ]
        );
    }

    protected function registerCustomCommands()
    {
        $customCommandPaths = FilesystemUtil::scanDir(APP_BASE_PATH . 'app/commands/*Command.php');

        foreach ($customCommandPaths as $commandPath) {
            $commandName = basename($commandPath, '.php');
            $commandNameWithNamespace = 'App\\commands\\' . $commandName;
            $this->symfonyApplication->add(new $commandNameWithNamespace);
        }
    }

    protected function registerConfiguredCommands()
    {
        $configuredCommands = Config::get('console.commands', []);

        foreach ($configuredCommands as $commandName) {
            $this->symfonyApplication->add(new $commandName);
        }
    }

    public function run()
    {
        $this->symfonyApplication->run();
    }
}
