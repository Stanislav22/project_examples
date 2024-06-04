<?php

namespace App\Helpers;

use App\Customers\Models\RoundingBase;
use App\Customers\Models\RoundingRule;

class RoundPrice
{
    /**
     * Round price
     *
     * @param float $amount
     * @param string $base
     * @param string $rule
     * @return float
     */
    public static function round($amount, $base, $rule)
    {
        if (Amount::isZero($amount)) {
            return 0;
        }

        $str = sprintf('%0.4f', $amount);
        list($dollars, $cents, $leftovers) = sscanf($str, '%d.%2d%2d');

        if ($base === RoundingBase::ONE_CENT)
        {
            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 0) => $cents + 1,
                    default => $cents,
                };
            }

            if ($dollars === 0 && $cents === 0) {
                $cents = 1;
            }
        }
        elseif ($base === RoundingBase::FIVE_CENTS)
        {
            $tail = $cents % 10;
            $cents = $cents - $tail;
            $leftovers = $leftovers + 100 * $tail;

            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 500) => $cents + 10,
                    ($leftovers > 0) => $cents + 5,
                    default => $cents,
                };
            } elseif ($rule === RoundingRule::ROUND_DOWN) {
                $cents = match(true) {
                    ($leftovers >= 500) => $cents + 5,
                    default => $cents,
                };
            }

            if ($dollars === 0 && $cents < 5) {
                $cents = 5;
            }
        }
        elseif ($base === RoundingBase::TEN_CENTS)
        {
            $tail = $cents % 10;
            $cents = $cents - $tail;
            $leftovers = $leftovers + 100 * $tail;

            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 0) => $cents + 10,
                    default => $cents,
                };
            }

            if ($dollars === 0 && $cents < 10) {
                $cents = 10;
            }
        }
        elseif ($base === RoundingBase::HALF_A_DOLLAR)
        {
            $leftovers = $leftovers + 100 * $cents;

            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 5000) => 100,
                    ($leftovers > 0) => 50,
                    default => 0,
                };
            } elseif ($rule === RoundingRule::ROUND_DOWN) {
                $cents = match(true) {
                    ($leftovers >= 5000) => 50,
                    default => 0,
                };
            }

            if ($dollars === 0 && $cents < 50) {
                $cents = 50;
            }
        }
        elseif ($base === RoundingBase::HALF_A_DOLLAR_STAR)
        {
            $leftovers = $leftovers + 100 * $cents;

            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 9500) => 100,
                    ($leftovers > 5000) => 95,
                    ($leftovers > 0) => 50,
                    default => 0,
                };
            } elseif ($rule === RoundingRule::ROUND_DOWN) {
                $cents = match(true) {
                    ($leftovers >= 9500) => 95,
                    ($leftovers >= 5000) => 50,
                    default => 0,
                };
            }

            if ($dollars === 0 && $cents < 50) {
                $cents = 50;
            }
        }
        elseif ($base === RoundingBase::ONE_DOLLAR)
        {
            $leftovers = $leftovers + 100 * $cents;

            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 0) => 100,
                    default => 0,
                };
            } elseif ($rule === RoundingRule::ROUND_DOWN) {
                $cents = 0;
            }

            if ($dollars === 0 && $cents < 100) {
                $cents = 100;
            }
        }
        elseif ($base === RoundingBase::ONE_DOLLAR_STAR)
        {
            $leftovers = $leftovers + 100 * $cents;

            if ($rule === RoundingRule::ROUND_UP) {
                $cents = match(true) {
                    ($leftovers > 9500) => 100,
                    ($leftovers > 0) => 95,
                    default => 0,
                };
            } elseif ($rule === RoundingRule::ROUND_DOWN) {
                $cents = match(true) {
                    ($leftovers >= 9500) => 95,
                    default => 0,
                };
            }

            if ($dollars === 0 && $cents < 95) {
                $cents = 95;
            }
        }

        return Amount::add($dollars, $cents / 100);
    }
}
