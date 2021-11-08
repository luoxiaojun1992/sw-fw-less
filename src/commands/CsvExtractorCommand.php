<?php

namespace SwFwLess\commands;

use SwFwLess\components\utils\excel\Csv;
use Symfony\Component\Console\Input\InputOption;

class CsvExtractorCommand extends AbstractCommand
{
    public $signature = 'csv:extractor';

    protected $csvFilePath;

    protected $withBom = false;

    protected $readBufferMemory = 2097152;

    protected $writeBufferMemory = 2097152;

    protected $enableMemoryMapping = false;

    protected $csvDelimiter = ',';

    protected $csvEnclosure = '"';

    protected $csvEscape = '\\';

    protected function configure()
    {
        $this->addOption(
            'file_path',
            'file_path',
            InputOption::VALUE_OPTIONAL,
            'CSV file path',
            $this->csvFilePath
        );
        $this->addOption(
            'with_bom',
            'with_bom',
            InputOption::VALUE_OPTIONAL,
            'If with bom',
            $this->withBom
        );
        $this->addOption(
            'read_mem_buffer',
            'read_mem_buffer',
            InputOption::VALUE_OPTIONAL,
            'Read memory buffer',
            $this->readBufferMemory
        );
        $this->addOption(
            'write_mem_buffer',
            'write_mem_buffer',
            InputOption::VALUE_OPTIONAL,
            'Write memory buffer',
            $this->writeBufferMemory
        );
        $this->addOption(
            'enable_mmap',
            'enable_mmap',
            InputOption::VALUE_OPTIONAL,
            'Enable memory mapping',
            $this->enableMemoryMapping
        );
        $this->addOption(
            'csv_delimiter',
            'csv_delimiter',
            InputOption::VALUE_OPTIONAL,
            'CSV delimiter',
            $this->csvDelimiter
        );
        $this->addOption(
            'csv_enclosure',
            'csv_enclosure',
            InputOption::VALUE_OPTIONAL,
            'CSV enclosure',
            $this->csvEnclosure
        );
        $this->addOption(
            'csv_escape',
            'csv_escape',
            InputOption::VALUE_OPTIONAL,
            'CSV escape',
            $this->csvEscape
        );
        $this->addOption(
            'handler',
            'handler',
            InputOption::VALUE_OPTIONAL,
            'Csv handler'
        );
    }

    protected function csvHandler($line)
    {
        $this->output->writeln($line);
    }

    protected function handle()
    {
        $csvUtil = Csv::createFromFilePath(
            $this->input->getOption('file_path'),
            true,
            false,
            boolval(intval($this->input->getOption('with_bom'))),
            intval($this->input->getOption('read_mem_buffer')),
            intval($this->input->getOption('write_mem_buffer')),
            boolval(intval($this->input->getOption('enable_mmap')))
        );

        $csvDelimiter = $this->input->getOption('csv_delimiter');
        $csvEnclosure = $this->input->getOption('csv_enclosure');
        $csvEscape = $this->input->getOption('csv_escape');

        while (!is_null($line = $csvUtil->getCsv(
            $csvDelimiter, $csvEnclosure, $csvEscape
        ))) {
            $handler = $this->input->getOption('handler');
            if (!is_null($handler)) {
                $handler = json_decode($handler);
            } else {
                $handler = [$this, 'csvHandler'];
            }
            $output = call_user_func_array($handler, ['line' => $line]);
            if (!is_null($output)) {
                $this->output->writeln($output);
            }
        }
    }
}
