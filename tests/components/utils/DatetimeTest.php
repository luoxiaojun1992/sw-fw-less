<?php

class DatetimeTest extends \PHPUnit\Framework\TestCase
{
    protected function rangeDates($yearMonthRangeMapping, $yearRange)
    {
        $dates = [];

        for ($i = $yearRange[0]; $i <= $yearRange[1]; ++$i) {
            $monthRange = $yearMonthRangeMapping[$i] ?? [1, 12];
            for ($j = $monthRange[0]; $j <= $monthRange[1]; ++$j) {
                if ($j < 10) {
                    $monthStr = '0' . ((string)$j);
                } else {
                    $monthStr = (string)$j;
                }
                $dates[] = ((string)$i) . '_' . $monthStr;
            }
        }

        return $dates;
    }

    public function testMonthIterator()
    {
        $this->assertEquals(
            $this->rangeDates(
                [
                    '2018' => [3, 12],
                    '2020' => [1, 5],
                ],
                [2018, 2020]
            ),
            \SwFwLess\components\utils\Datetime::monthIterator(
                '2018-03-25',
                '2020-05-12',
                'Y_m'
            )
        );

        $this->assertEquals(
            $this->rangeDates(
                [
                    '2017' => [1, 12],
                    '2020' => [1, 12],
                ],
                [2017, 2020]
            ),
            \SwFwLess\components\utils\Datetime::monthIterator(
                '2017-01-13',
                '2020-12-22',
                'Y_m'
            )
        );

        $this->assertEquals(
            $this->rangeDates(
                [
                    '2020' => [7, 9],
                ],
                [2020, 2020]
            ),
            \SwFwLess\components\utils\Datetime::monthIterator(
                '2020-07-18',
                '2020-09-03',
                'Y_m'
            )
        );

        $this->assertEquals(
            $this->rangeDates(
                [
                    '2019' => [1, 12],
                ],
                [2019, 2019]
            ),
            \SwFwLess\components\utils\Datetime::monthIterator(
                '2019-01-09',
                '2019-12-03',
                'Y_m'
            )
        );
    }
}
