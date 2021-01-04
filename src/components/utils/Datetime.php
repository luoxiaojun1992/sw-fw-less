<?php

namespace SwFwLess\components\utils;

use Carbon\Carbon;

class Datetime
{
    public static function lastWeeks(int $weeks, $fromDate = null)
    {
        if (is_null($fromDate)) {
            return [
                \Carbon\Carbon::now()->endOfDay()->subWeek($weeks)->startOfWeek()->toDateString(),
                \Carbon\Carbon::now()->endOfDay()->subWeek(1)->endOfWeek()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->endOfDay()->subWeek($weeks)->startOfWeek()->toDateString(),
                static::toDateObj($fromDate)->endOfDay()->subWeek(1)->endOfWeek()->toDateString(),
            ];
        }
    }

    public static function lastWeek($fromDate = null)
    {
        return static::lastWeeks(1, $fromDate);
    }

    public static function lastMonths(int $months, $fromDate = null)
    {
        if (is_null($fromDate)) {
            return [
                \Carbon\Carbon::now()->endOfDay()->subMonthNoOverflow($months)->startOfMonth()->toDateString(),
                \Carbon\Carbon::now()->endOfDay()->subMonthNoOverflow(1)->endOfMonth()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->endOfDay()->subMonthNoOverflow($months)->startOfMonth()->toDateString(),
                static::toDateObj($fromDate)->endOfDay()->subMonthNoOverflow(1)->endOfMonth()->toDateString(),
            ];
        }
    }

    public static function lastMonth($fromDate = null)
    {
        return static::lastMonths(1, $fromDate);
    }

    public static function lastQuarters(int $quarters, $fromDate = null)
    {
        if (is_null($fromDate)) {
            return [
                \Carbon\Carbon::now()->endOfDay()->subQuarter($quarters)->startOfQuarter()->toDateString(),
                \Carbon\Carbon::now()->endOfDay()->subQuarter(1)->endOfQuarter()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->endOfDay()->subQuarter($quarters)->startOfQuarter()->toDateString(),
                static::toDateObj($fromDate)->endOfDay()->subQuarter(1)->endOfQuarter()->toDateString(),
            ];
        }
    }

    public static function lastQuarter($fromDate = null)
    {
        return static::lastQuarters(1, $fromDate);
    }

    public static function lastYears(int $years, $fromDate = null)
    {
        if (is_null($fromDate)) {
            return [
                \Carbon\Carbon::now()->endOfDay()->subYear($years)->startOfYear()->toDateString(),
                \Carbon\Carbon::now()->endOfDay()->subYear(1)->endOfYear()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->endOfDay()->subYear($years)->startOfYear()->toDateString(),
                static::toDateObj($fromDate)->endOfDay()->subYear(1)->endOfYear()->toDateString(),
            ];
        }
    }

    public static function lastYear($fromDate = null)
    {
        return static::lastYears(1, $fromDate);
    }

    public static function lastTwoMonths($fromDate = null)
    {
        return static::lastMonths(2, $fromDate);
    }

    public static function lastTwoQuarters($fromDate = null)
    {
        return static::lastQuarters(2, $fromDate);
    }

    public static function lastDays(int $days, $fromDate = null)
    {
        if (is_null($fromDate)) {
            return [
                \Carbon\Carbon::now()->endOfDay()->subDays($days)->toDateString(),
                \Carbon\Carbon::now()->endOfDay()->subDays(1)->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->endOfDay()->subDays($days)->toDateString(),
                static::toDateObj($fromDate)->endOfDay()->subDays(1)->toDateString(),
            ];
        }
    }

    public static function lastSevenDays($fromDate = null)
    {
        return static::lastDays(7, $fromDate);
    }

    public static function lastFifteenDays($fromDate = null)
    {
        return static::lastDays(15, $fromDate);
    }

    public static function lastThirtyDays($fromDate = null)
    {
        return static::lastDays(30, $fromDate);
    }

    public static function yesterday()
    {
        return static::lastDays(1);
    }

    public static function toDateObj($date)
    {
        return Carbon::parse($date);
    }

    public static function toIntDate($date)
    {
        return intval(str_replace('-', '', $date));
    }

    public static function toStrDate($intDate)
    {
        $year = (int)($intDate / 10000);
        $month = ((int)($intDate / 100)) % 100;
        $day = $intDate % 100;

        return str_pad((string)$year, 4, '0', STR_PAD_LEFT) . '-' .
            str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-' .
            str_pad((string)$day, 2, '0', STR_PAD_LEFT);
    }

    public static function validDate($date)
    {
        return static::toDateObj($date)->toDateString() === $date;
    }

    /**
     * @param string $startDate Y-m-d
     * @param string $endDate Y-m-d
     * @return array
     */
    public static function sequentialPeriod(string $startDate, string $endDate)
    {
        if (static::isSeveralRealYears($startDate, $endDate)) {
            return static::lastYears(
                static::diffRealYears($startDate, $endDate),
                $startDate
            );
        } elseif (static::isSeveralRealQuarters($startDate, $endDate)) {
            return static::lastQuarters(
                static::diffRealQuarters($startDate, $endDate),
                $startDate
            );
        } elseif (static::isSeveralRealMonths($startDate, $endDate)) {
            return static::lastMonths(
                static::diffRealMonths($startDate, $endDate),
                $startDate
            );
        } elseif (static::isSeveralRealWeeks($startDate, $endDate)) {
            return static::lastWeeks(
                static::diffRealWeeks($startDate, $endDate),
                $startDate
            );
        } else {
            $start = static::toDateObj($startDate)->startOfDay();
            $end = static::toDateObj($endDate)->endOfDay();
            $diff = $end->diffInDays($start);
            $sequentialStart = $start->subDays($diff + 1)->toDateString();
            $sequentialEnd = $start->addDays($diff)->toDateString();
            return [$sequentialStart, $sequentialEnd];
        }
    }

    public static function isDaily(string $startDate, string $endDate)
    {
        return $startDate === $endDate;
    }

    public static function diffRealWeeks(string $startDate, string $endDate)
    {
        return (static::toDateObj($endDate)->endOfDay()->diffInWeeks(static::toDateObj($startDate)->startOfDay()) + 1);
    }

    public static function isSeveralRealWeeks(string $startDate, string $endDate, $weeks = null)
    {
        if (!static::toDateObj($startDate)->isMonday()) {
            return false;
        }

        if (!static::toDateObj($endDate)->isSunday()) {
            return false;
        }

        if (!is_null($weeks)) {
            if (static::diffRealWeeks($startDate, $endDate) !== $weeks) {
                return false;
            }
        }

        return true;
    }

    public static function diffRealMonths(string $startDate, string $endDate)
    {
        $startDateObj = static::toDateObj($startDate);
        $endDateObj = static::toDateObj($endDate);
        $startDateMonth = $startDateObj->month;
        $endDateMonth = $endDateObj->month;
        $startDateYear = $startDateObj->year;
        $endDateYear = $endDateObj->year;
        $diffYear = $endDateYear - $startDateYear;
        if ($diffYear === 0) {
            return $endDateMonth - $startDateMonth + 1;
        } else {
            $diffMonth = $endDateMonth + (12 - $startDateMonth + 1);
            if ($diffYear > 1) {
                $diffMonth += ($endDateYear - $startDateYear - 1) * 12;
            }
        }

        return $diffMonth;
    }

    public static function isSeveralRealMonths(string $startDate, string $endDate, $months = null)
    {
        if (static::toDateObj($startDate)->day !== 1) {
            return false;
        }

        if (!static::toDateObj($endDate)->isLastOfMonth()) {
            return false;
        }

        if (!is_null($months)) {
            if (static::diffRealMonths($startDate, $endDate) !== $months) {
                return false;
            }
        }

        return true;
    }

    public static function diffRealQuarters(string $startDate, string $endDate)
    {
        return (static::diffRealMonths($startDate, $endDate) / 3);
    }

    public static function isSeveralRealQuarters(string $startDate, string $endDate, $quarters = null)
    {
        if (!in_array(static::toDateObj($startDate)->month, [1, 4, 7, 10])) {
            return false;
        }

        if (static::toDateObj($startDate)->day !== 1) {
            return false;
        }

        if (!in_array(static::toDateObj($endDate)->month, [3, 6, 9, 12])) {
            return false;
        }

        if (!static::toDateObj($endDate)->isLastOfMonth()) {
            return false;
        }

        if (!is_null($quarters)) {
            if (static::diffRealQuarters($startDate, $endDate) !== $quarters) {
                return false;
            }
        }

        return true;
    }

    public static function diffRealYears(string $startDate, string $endDate)
    {
        return (static::toDateObj($endDate)->endOfDay()->diffInYears(static::toDateObj($startDate)->startOfDay()) + 1);
    }

    public static function isSeveralRealYears(string $startDate, string $endDate, $years = null)
    {
        if (static::toDateObj($startDate)->month !== 1) {
            return false;
        }

        if (static::toDateObj($startDate)->day !== 1) {
            return false;
        }

        if (static::toDateObj($endDate)->month !== 12) {
            return false;
        }

        if (!static::toDateObj($endDate)->isLastOfMonth()) {
            return false;
        }

        if (!is_null($years)) {
            if (static::diffRealYears($startDate, $endDate) !== $years) {
                return false;
            }
        }

        return true;
    }

    public static function comparePeriod($period1, $period2)
    {
        return ($period1[0] === $period2[0]) && ($period1[1] === $period2[1]);
    }

    public static function compareSequentialPeriod($period1, $period2)
    {
        if(static::comparePeriod($period1, $period2)) {
            return true;
        }

        $sequentialPeriod = static::sequentialPeriod(...$period2);
        if(static::comparePeriod($period1, $sequentialPeriod)) {
            return true;
        }

        return false;
    }

    public static function isFixedPeriod(string $startDate, string $endDate)
    {
        $period = [$startDate, $endDate];

        if (static::compareSequentialPeriod($period, static::lastWeek())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::lastMonth())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::lastQuarter())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::lastYear())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::lastTwoMonths())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::lastTwoQuarters())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::lastThirtyDays())) {
            return true;
        }

        if (static::compareSequentialPeriod($period, static::yesterday())) {
            return true;
        }

        return false;
    }

    public static function monthIterator(string $startDate, string $endDate, $format = 'Y_m')
    {
        $dateList = [];

        $now = static::toDateObj($startDate)->startOfMonth()->startOfDay();
        $endDateObj = static::toDateObj($endDate)->endOfMonth()->endOfDay();
        while ($now->lessThanOrEqualTo($endDateObj)) {
            $dateList[] = $now->format($format);
            $now->addMonthsNoOverflow(1);
        }

        return $dateList;
    }
}
