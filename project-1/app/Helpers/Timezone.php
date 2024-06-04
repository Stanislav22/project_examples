<?php

namespace App\Helpers;

use App\Facades\Settings;
use Carbon\Carbon;

class Timezone
{
    /**
     * Apply timezone to the given time
     * 
     * @param string|Carbon|\DateTime $datetime
     * @param string $tz
     * @return Carbon
     */
    public static function on($datetime, $tz = null)
    {
        $carbon = static::instance($datetime);
        
        if ($tz === null) {
            $tz = Settings::get('general.time_zone');
        }

        $carbon->setTimezone($tz);

        return $carbon;
    }

    /**
     * Shift timezone to UTC on the given time
     * 
     * @param string|Carbon|\DateTime $datetime
     * @param string $tz
     * @return Carbon
     */
    public static function off($datetime, $tz = null)
    {
        $carbon = static::instance($datetime);
        
        if ($tz === null) {
            $tz = Settings::get('general.time_zone');
        }

        $carbon->shiftTimezone($tz);
        $carbon->setTimezone('UTC');

        return $carbon;
    }

    /**
     * Get current time in certain TZ
     * 
     * @param string $tz
     * @return Carbon
     */
    public static function now($tz = null)
    {
        if ($tz === null) {
            $tz = Settings::get('general.time_zone');
        }

        return Carbon::now($tz);
    }

    /**
     * Get start of the day in certain TZ relative to UTC
     * 
     * @param string $day
     * @param string $tz
     * @return Carbon
     */
    public static function startOfDay($day = null, $tz = null)
    {
        $time = $day
            ? static::instance($day)
            : Carbon::now();

        return static::off($time->startOfDay(), $tz);
    }

    /**
     * Get end of the day in certain TZ relative to UTC
     * 
     * @param string $day
     * @param string $tz
     * @return Carbon
     */
    public static function endOfDay($day = null, $tz = null)
    {
        $time = $day
            ? static::instance($day)
            : Carbon::now();

        return static::off($time->endOfDay(), $tz);
    }

    /**
     * Get start of the month in certain TZ relative to UTC
     * 
     * @param string $month
     * @param string $tz
     * @return Carbon
     */
    public static function startOfMonth($month = null, $tz = null)
    {
        $time = $month
            ? static::instance($month)
            : Carbon::now();

        return static::off($time->startOfMonth(), $tz);
    }

    /**
     * Get end of the month in certain TZ relative to UTC
     * 
     * @param string $month
     * @param string $tz
     * @return Carbon
     */
    public static function endOfMonth($month = null, $tz = null)
    {
        $time = $month
            ? static::instance($month)
            : Carbon::now();

        return static::off($time->endOfMonth(), $tz);
    }

    /**
     * Get start of the previous month in certain TZ relative to UTC
     * 
     * @param string $month
     * @param string $tz
     */
    public static function startOfPreviousMonth($month = null, $tz = null)
    {
        $time = $month
            ? static::instance($month)
            : Carbon::now();

        return static::off($time->subMonth()->startOfMonth(), $tz);
    }

    /**
     * Get end of the previous month in certain TZ relative to UTC
     * 
     * @param string $month
     * @param string $tz
     */
    public static function endOfPreviousMonth($month = null, $tz = null)
    {
        $time = $month
            ? static::instance($month)
            : Carbon::now();

        return static::off($time->subMonth()->endOfMonth(), $tz);
    }

    /**
     * Create cloned nstance of datetime
     * 
     * @param string|Carbon|\DateTime $datetime
     * @return Carbon
     */
    protected static function instance($datetime)
    {
        if ($datetime instanceof Carbon) {
            return clone $datetime;
        }
        
        if ($datetime instanceof \DateTime) {
            return Carbon::instance($datetime);
        }
        
        return Carbon::parse($datetime);
    }
}