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

use SwFwLess\models\AbstractPDOModel;

class %s extends AbstractPDOModel
{
    protected static $table = '%s';
    
    protected static $connectionName = %s;
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
        $this->addOption(
            'connection',
            'connection',
            InputOption::VALUE_OPTIONAL,
            'DB connection',
            null
        );
    }

    protected function handle()
    {
        $modelFilePath = $this->input->getOption('path');

        $modelFileDir = dirname($modelFilePath);

        $modelNamespace = str_replace('/', '\\', trim($modelFileDir, '/'));

        $modelClassName = basename($modelFilePath, '.php');

        $connection = $this->input->getOption('connection');

        $modelFileContent = sprintf(
            static::MODEL_FILE_TPL, $modelNamespace, $modelClassName,
            $this->input->getOption('table'),
            is_null($connection) ? 'null' : ('\''. $connection . '\'')
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
