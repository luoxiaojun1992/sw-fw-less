<?php

namespace SwFwLessTests\components\utils\excel;

class CsvBench
{
    public function benchNativeCsv()
    {
        $filePath = __DIR__ . '/../../../output/test_native_csv.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        touch($filePath);

        $csvFile = fopen($filePath, 'w+');

        mt_srand(time());

        for ($row = 0; $row < 100000; ++$row) {
            $rowData = [];
            for ($col = 0; $col < 10; ++$col) {
                $rowData[] = mt_rand(10000, 99999);
            }
            fputcsv($csvFile, $rowData);
        }

        unlink($filePath);
    }

    public function benchCsvUtil()
    {
        $filePath = __DIR__ . '/../../../output/test_csv_util.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        touch($filePath);

        $csvFile = \SwFwLess\components\utils\excel\Csv::createFromFilePath(
            $filePath, false, true, false, 2097152,
            2097152, false
        );

        mt_srand(time());

        for ($row = 0; $row < 100000; ++$row) {
            $rowData = [];
            for ($col = 0; $col < 10; ++$col) {
                $rowData[] = mt_rand(10000, 99999);
            }
            $csvFile->putCsv($rowData);
        }

        unlink($filePath);
    }

    public function benchMemoryMappedCsvUtil()
    {
        $filePath = __DIR__ . '/../../../output/test_mmap_csv_util.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        touch($filePath);

        $csvFile = \SwFwLess\components\utils\excel\Csv::createFromFilePath(
            $filePath, false, true, false, 2097152,
            2097152, true
        );
        $csvFile->setMaxWriteBufferSize(1000);

        mt_srand(time());

        for ($row = 0; $row < 100000; ++$row) {
            $rowData = [];
            for ($col = 0; $col < 10; ++$col) {
                $rowData[] = mt_rand(10000, 99999);
            }
            $csvFile->putCsv($rowData);
        }

        unlink($filePath);
    }
}
