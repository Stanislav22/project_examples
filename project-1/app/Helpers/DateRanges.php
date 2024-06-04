<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateRanges
{
    const RECENT = 'recent';
    const YESTERDAY = 'yesterday';
    const WEEK = 'week';

    /**
     * Get range of certain day
     * 
     * @param mixed $date
     * @param mixed $tz
     * @return array
     */
    public static function day($date, $tz = null)
    {
        return [
            Timezone::on($date, $tz)->startOfDay()->setTimezone('UTC'),
            Timezone::on($date, $tz)->endOfDay()->setTimezone('UTC'),
        ];
    }

    /**
     * Get recent range
     * 
     * @param mixed $tz
     * @return array
     */
    public static function recent($tz = null)
    {
        return [
            Timezone::now($tz)->startOfDay()->setTimezone('UTC'),
            Timezone::now($tz)->setTimezone('UTC'),
        ];
    }

    /**
     * Get yesterday range
     * 
     * @param mixed $tz
     * @return array
     */
    public static function yesterday($tz = null)
    {
        return [
            Timezone::now($tz)->subDay()->startOfDay()->setTimezone('UTC'),
            Timezone::now($tz)->subDay()->endOfDay()->setTimezone('UTC'),
        ];
    }

    /**
     * Get week range
     * 
     * @param mixed $tz
     * @return array
     */
    public static function week($tz = null)
    {
        return [
            Timezone::now($tz)->subWeek()->startOfDay()->setTimezone('UTC'),
            Timezone::now($tz)->setTimezone('UTC'),
        ];
    }

    /**
     * Get specific range
     * 
     * @param string $type
     * @param mixed $tz
     * @return array
     */
    public static function get($type, $tz = null)
    {
        return match ($type) {
            self::RECENT => static::recent($tz),
            self::YESTERDAY => static::yesterday($tz),
            self::WEEK => static::week($tz),
            default => null,
        };
    }
}