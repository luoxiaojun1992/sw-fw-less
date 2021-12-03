<?php

namespace SwFwLess\commands\generators;

use League\Flysystem\Adapter\Local;
use SwFwLess\commands\AbstractCommand;
use SwFwLess\facades\File;
use Symfony\Component\Console\Input\InputOption;

class FacadeGenerator extends AbstractCommand
{
    const FACADE_FILE_TPL = <<<'EOF'
<?php

namespace App\%s;

use SwFwLess\facades\AbstractFacade;

class %s extends AbstractFacade
{
    protected static function getAccessor()
    {
        //TODO implements getAccessor
        return null;
    }
}

EOF;

    public $signature = 'generator:facade';

    protected function configure()
    {
        $this->addOption(
            'path',
            'path',
            InputOption::VALUE_REQUIRED,
            'Facade file path',
            null
        );
    }

    protected function handle()
    {
        $facadeFilePath = $this->input->getOption('path');

        $facadeFileDir = dirname($facadeFilePath);

        $facadeNamespace = str_replace('/', '\\', trim($facadeFileDir, '/'));

        $facadeClassName = basename($facadeFilePath, '.php');

        $facadeFileContent = sprintf(
            static::FACADE_FILE_TPL, $facadeNamespace, $facadeClassName
        );

        if (File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            File::appPath()
        )->put($facadeFilePath, $facadeFileContent)) {
            $this->output->writeln('<info>Generated successfully.</info>');
        } else {
            $this->output->writeln('<error>Failed.</error>');
        }
    }
}
