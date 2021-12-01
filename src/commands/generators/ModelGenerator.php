<?php

namespace SwFwLess\commands\generators;

use League\Flysystem\Adapter\Local;
use SwFwLess\commands\AbstractCommand;
use SwFwLess\facades\File;
use Symfony\Component\Console\Input\InputOption;

class ModelGenerator extends AbstractCommand
{
    const MODEL_FILE_TPL = <<<'EOF'
<?php

namespace App\%s;

class %s extends AbstractPDOModel
{
    protected static $table = '%s';
}
EOF;

    public $signature = 'generator:model';

    protected function configure()
    {
        $this->addOption(
            'path',
            'path',
            InputOption::VALUE_REQUIRED,
            'Model file path',
            null
        );
        $this->addOption(
            'table',
            'table',
            InputOption::VALUE_REQUIRED,
            'Table name',
            null
        );
    }

    protected function handle()
    {
        $modelFilePath = $this->input->getOption('path');

        $modelFileDir = dirname($modelFilePath);
        $modelNamespace = str_replace('/', '\\', trim('/', $modelFileDir));

        $modelClassName = basename($modelFilePath, '.php');

        $modelFileContent = sprintf(
            static::MODEL_FILE_TPL, $modelNamespace, $modelClassName,
            $this->input->getOption('table')
        );

        if (File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            File::appPath()
        )->put($modelFilePath, $modelFileContent)) {
            $this->output->writeln('<info>Generated successfully.</info>');
        } else {
            $this->output->writeln('<error>Failed.</error>');
        }
    }
}
