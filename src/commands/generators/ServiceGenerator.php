<?php

namespace SwFwLess\commands\generators;

use League\Flysystem\Adapter\Local;
use SwFwLess\commands\AbstractCommand;
use SwFwLess\facades\File;
use Symfony\Component\Console\Input\InputOption;

class ServiceGenerator extends AbstractCommand
{
    const SERVICE_FILE_TPL = <<<'EOF'
<?php

namespace App\%s;

use SwFwLess\services\BaseService;

class %s extends BaseService
{
    //
}

EOF;

    public $signature = 'generator:service';

    protected function configure()
    {
        $this->addOption(
            'path',
            'path',
            InputOption::VALUE_REQUIRED,
            'Service file path',
            null
        );
    }

    protected function handle()
    {
        $serviceFilePath = $this->input->getOption('path');

        $serviceFileDir = dirname($serviceFilePath);

        $serviceNamespace = str_replace('/', '\\', trim($serviceFileDir, '/'));

        $serviceClassName = basename($serviceFilePath, '.php');

        $serviceFileContent = sprintf(
            static::SERVICE_FILE_TPL, $serviceNamespace, $serviceClassName
        );

        if (File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            File::appPath()
        )->put($serviceFilePath, $serviceFileContent)) {
            $this->output->writeln('<info>Generated successfully.</info>');
        } else {
            $this->output->writeln('<error>Failed.</error>');
        }
    }
}
