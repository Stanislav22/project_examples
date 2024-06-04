<?php

namespace App\Helpers;

class Amount
{
    const PRECISION = 2;
    const DOUBLE_PRECISION = 4;

    /**
     * Round amount
     *
     * @param float $a
     * @param int $precision
     * @return float
     */
    public static function round($a, $precision = self::PRECISION)
    {
        return round($a, $precision);
    }

    /**
     * Get sum of amounts
     *
     * @return float
     */
    public static function sum()
    {
        return round(array_sum(func_get_args()), self::PRECISION);
    }

    /**
     * Add amounts together
     *
     * @return float
     */
    public static function add($a, $b, $precision = self::PRECISION)
    {
        return round($a + $b, $precision);
    }

    /**
     * Subtract amount preventing negative values
     *
     * @param float $a
     * @param float $b
     * @param int $precision
     * @return float
     */
    public static function sub($a, $b, $precision = self::PRECISION)
    {
        return round(max(0, $a - $b), $precision);
    }

    /**
     * Subtract amount allowing negativa values
     *
     * @param float $a
     * @param float $b
     * @param int $precision
     * @return float
     */
    public static function ssub($a, $b, $precision = self::PRECISION)
    {
        return round($a - $b, $precision);
    }

    /**
     * Multiply amounts
     *
     * @param float $a
     * @param float $b
     * @param int $precision
     * @return float
     */
    public static function mult($a, $b, $precision = self::PRECISION)
    {
        return round($a * $b, $precision);
    }

    /**
     * Divide amounts
     *
     * @param float $a
     * @param float $b
     * @param int $precision
     * @return float
     */
    public static function div($a, $b, $precision = self::PRECISION)
    {
        return static::isZero($b) ? 0 : round($a / $b, $precision);
    }

    /**
     * Calculate percent from amount
     *
     * @param float $a
     * @param float $percent
     * @param int $precision
     * @return float
     */
    public static function percentOf($a, $percent, $precision = self::PRECISION)
    {
        return round($a * $percent / 100, $precision);
    }

    /**
     * Calculate amount as percent
     *
     * @param float $a
     * @param float $b
     * @param int $precision
     * @return float
     */
    public static function toPercent($a, $b, $precision = self::PRECISION)
    {
        return static::isZero($b) ? 0 : round(100 * $a / $b, $precision);
    }

    /**
     * Add percent to amount
     *
     * @param float $a
     * @param float $percent
     * @param int $precision
     * @return float
     */
    public static function addPercent($a, $percent, $precision = self::PRECISION)
    {
        return round($a + $a * $percent / 100, $precision);
    }

    /**
     * Subtract percent from amount
     *
     * @param float $a
     * @param float $percent
     * @param int $precision
     * @return float
     */
    public static function subPercent($a, $percent, $precision = self::PRECISION)
    {
        return static::addPercent($a, -$percent, $precision);
    }

    /**
     * Check if amount equals to zero
     *
     * @param float $a
     * @return bool
     */
    public static function isZero($a)
    {
        return round($a, self::PRECISION) === 0.0;
    }
}
