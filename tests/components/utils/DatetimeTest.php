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

    public function testDaysIterator()
    {
        $this->assertCount(
            123,
            \SwFwLess\components\utils\Datetime::daysIterator(
                '2020-01-30',
                '2020-05-31'
            )
        );

        $this->assertCount(
            92,
            \SwFwLess\components\utils\Datetime::daysIterator(
                '2020-03-01',
                '2020-05-31'
            )
        );

        $this->assertCount(
            121,
            \SwFwLess\components\utils\Datetime::daysIterator(
                '2020-02-01',
                '2020-05-31'
            )
        );

        $this->assertCount(
            94,
            \SwFwLess\components\utils\Datetime::daysIterator(
                '2020-02-28',
                '2020-05-31'
            )
        );

        $this->assertCount(
            459,
            \SwFwLess\components\utils\Datetime::daysIterator(
                '2019-02-28',
                '2020-05-31'
            )
        );
    }

    public function testDiffDays()
    {
        $this->assertEquals(
            123,
            \SwFwLess\components\utils\Datetime::diffDays(
                '2020-01-30',
                '2020-05-31'
            )
        );

        $this->assertEquals(
            92,
            \SwFwLess\components\utils\Datetime::diffDays(
                '2020-03-01',
                '2020-05-31'
            )
        );

        $this->assertEquals(
            121,
            \SwFwLess\components\utils\Datetime::diffDays(
                '2020-02-01',
                '2020-05-31'
            )
        );

        $this->assertEquals(
            94,
            \SwFwLess\components\utils\Datetime::diffDays(
                '2020-02-28',
                '2020-05-31'
            )
        );

        $this->assertEquals(
            459,
            \SwFwLess\components\utils\Datetime::diffDays(
                '2019-02-28',
                '2020-05-31'
            )
        );
    }

    public function testRandomSleep()
    {
        $startTime = time();
        \SwFwLess\components\utils\Datetime::randomSleep(1, 3);
        $this->assertGreaterThanOrEqual(1, time() - $startTime);
    }

    public function testRandomUsleep()
    {
        $startTime = time();
        \SwFwLess\components\utils\Datetime::randomUsleep(1000000, 3000000);
        $this->assertGreaterThanOrEqual(1, time() - $startTime);
    }

    public function testNodesTimeOffset()
    {
        $sendTime = microtime(true);
        $receiveTime = $sendTime + 0.010;
        $rtt = 0.010;
        $nodesTimeOffset = \SwFwLess\components\utils\Datetime::nodesTimeOffset(
            $sendTime, $receiveTime, $rtt
        );
        $this->assertEquals(-0.010, $nodesTimeOffset['minOffset']);
        $this->assertEquals(0.0, $nodesTimeOffset['maxOffset']);
        $this->assertEquals(-0.005, $nodesTimeOffset['avgOffset']);

        $sendTime = microtime(true);
        $receiveTime = $sendTime + 0.005;
        $rtt = 0.010;
        $nodesTimeOffset = \SwFwLess\components\utils\Datetime::nodesTimeOffset(
            $sendTime, $receiveTime, $rtt
        );
        $this->assertEquals(-0.005, $nodesTimeOffset['minOffset']);
        $this->assertEquals(0.005, $nodesTimeOffset['maxOffset']);
        $this->assertEquals(0, $nodesTimeOffset['avgOffset']);

        $sendTime = microtime(true);
        $receiveTime = $sendTime + 0.006;
        $rtt = 0.010;
        $nodesTimeOffset = \SwFwLess\components\utils\Datetime::nodesTimeOffset(
            $sendTime, $receiveTime, $rtt
        );
        $this->assertEquals(-0.006, $nodesTimeOffset['minOffset']);
        $this->assertEquals(0.004, $nodesTimeOffset['maxOffset']);
        $this->assertEquals(-0.001, $nodesTimeOffset['avgOffset']);
    }
}
