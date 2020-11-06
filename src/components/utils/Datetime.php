<?php

namespace SwFwLess\components\utils;

use Carbon\Carbon;

class Datetime
{
    public static function lastWeeks(int $weeks, $fromDate = null)
    {
        if (is_null($fromDate)) {
            return [
                \Carbon\Carbon::now()->subWeek($weeks)->startOfWeek()->toDateString(),
                \Carbon\Carbon::now()->subWeek(1)->endOfWeek()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->subWeek($weeks)->startOfWeek()->toDateString(),
                static::toDateObj($fromDate)->subWeek(1)->endOfWeek()->toDateString(),
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
                \Carbon\Carbon::now()->subMonthNoOverflow($months)->startOfMonth()->toDateString(),
                \Carbon\Carbon::now()->subMonthNoOverflow(1)->endOfMonth()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->subMonthNoOverflow($months)->startOfMonth()->toDateString(),
                static::toDateObj($fromDate)->subMonthNoOverflow(1)->endOfMonth()->toDateString(),
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
                \Carbon\Carbon::now()->subQuarter($quarters)->startOfQuarter()->toDateString(),
                \Carbon\Carbon::now()->subQuarter(1)->endOfQuarter()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->subQuarter($quarters)->startOfQuarter()->toDateString(),
                static::toDateObj($fromDate)->subQuarter(1)->endOfQuarter()->toDateString(),
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
                \Carbon\Carbon::now()->subYear($years)->startOfYear()->toDateString(),
                \Carbon\Carbon::now()->subYear(1)->endOfYear()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->subYear($years)->startOfYear()->toDateString(),
                static::toDateObj($fromDate)->subYear(1)->endOfYear()->toDateString(),
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
                \Carbon\Carbon::now()->subDays($days)->startOfDay()->toDateString(),
                \Carbon\Carbon::now()->subDays(1)->endOfDay()->toDateString(),
            ];
        } else {
            return [
                static::toDateObj($fromDate)->subDays($days)->startOfDay()->toDateString(),
                static::toDateObj($fromDate)->subDays(1)->endOfDay()->toDateString(),
            ];
        }
    }

    public static function lastThirtyDays($fromDate = null)
    {
        return static::lastDays(30, $fromDate);
    }

    public static function yesterday()
    {
        $yesterday = Carbon::yesterday()->startOfDay()->toDateString();
        return [$yesterday, $yesterday];
    }

    public static function toDateObj($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date);
    }

    public static function toIntDate($date)
    {
        return intval(str_replace('-', '', $date));
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
        if (static::isSeveralRealWeeks($startDate, $endDate)) {
            return static::lastWeeks(
                static::diffRealWeeks($startDate, $endDate),
                $startDate
            );
        } elseif (static::isSeveralRealMonths($startDate, $endDate)) {
            return static::lastMonths(
                static::diffRealMonths($startDate, $endDate),
                $startDate
            );
        } elseif (static::isSeveralRealQuarters($startDate, $endDate)) {
            return static::lastQuarters(
                static::diffRealQuarters($startDate, $endDate),
                $startDate
            );
        } elseif (static::isSeveralRealYears($startDate, $endDate)) {
            return static::lastYears(
                static::diffRealYears($startDate, $endDate),
                $startDate
            );
        } else {
            $start = static::toDateObj($startDate);
            $end = static::toDateObj($endDate);
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
        return (static::toDateObj($endDate)->diffInWeeks(static::toDateObj($startDate)) + 1);
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
        return (static::toDateObj($endDate)->diffInMonths(static::toDateObj($startDate)) + 1);
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
        return ((static::toDateObj($endDate)->diffInMonths(static::toDateObj($startDate)) + 1) / 3);
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
        return (static::toDateObj($endDate)->diffInYears(static::toDateObj($startDate)) + 1);
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

        $now = static::toDateObj($startDate)->startOfMonth();
        $endDateObj = static::toDateObj($endDate)->endOfMonth();
        while ($now->lessThanOrEqualTo($endDateObj)) {
            $dateList[] = $now->format($format);
            $now->addMonthsNoOverflow(1);
        }

        return $dateList;
    }
}
