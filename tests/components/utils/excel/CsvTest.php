<?php

class CsvTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     */
    public function testPutCsv()
    {
        $this->putCsv();
        $this->putCsv(true);
    }

    /**
     * @param false $withBom
     * @throws Exception
     */
    public function putCsv($withBom = false)
    {
        $filePath = __DIR__ . '/../../../output/test.csv';

        if (file_exists($filePath)) {
            $this->assertTrue(unlink($filePath));
        }

        $this->assertTrue(touch($filePath));

        $csvFile = \SwFwLess\components\utils\excel\Csv::createFromFilePath(
            $filePath, false, true
        )->withBom($withBom);

        $rowCount = 10000;
        $colCount = 10;

        $row = [];
        for ($colNum = 0; $colNum < $colCount; ++$colNum) {
            $row[] = 'col' . ((string)($colNum + 1));
        }
        $putRes = $csvFile->putCsv($row);
        $this->assertIsInt($putRes);
        $this->assertGreaterThan(0, $putRes);

        mt_srand(time());
        for ($rowNum = 0; $rowNum < $rowCount; ++$rowNum) {
            $row = [];
            for ($colNum = 0; $colNum < $colCount; ++$colNum) {
                $row[] = mt_rand(0, intval(pow(10, 5))) / mt_getrandmax();
            }

            $putRes = $csvFile->putCsv($row);
            $this->assertIsInt($putRes);
            $this->assertGreaterThan(0, $putRes);
        }

        $csvFile->flush()->closeFile();

        unlink($filePath);
    }

    /**
     * @throws Exception
     */
    public function testGetCsv()
    {
        $this->getCsv();
        $this->getCsv(true);
    }

    /**
     * @param false $withBom
     * @throws Exception
     */
    public function getCsv($withBom = false)
    {
        $filePath = __DIR__ . '/../../../stubs/components/utils/excel/test.csv';

        $csvFile = \SwFwLess\components\utils\excel\Csv::createFromFilePath(
            $filePath, true, false
        )->withBom($withBom);

        $rowCount = 101;
        for ($rowNum = 0; $rowNum < $rowCount; ++$rowNum) {
            $this->assertArrayHasKey(0, $csvFile->getCsv());
        }

        $this->assertNull($csvFile->getCsv());
    }
}
