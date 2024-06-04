<?php

namespace App\Helpers;

use App\Contracts\SettingsManager;
use App\Facades\Settings;
use App\Orders\Models\BaseOrder;
use App\Orders\Models\BaseOrderItem;
use App\Products\Models\Enums\ProductType;
use App\Models\Symbol;
use Carbon\Carbon;

class Format
{
    const CONTEXT_PREPARATION = 'preparation';
    const CONTEXT_ORDER_QTY = 'order_qty';
    const CONTEXT_PRODUCT_QTY = 'product_qty';

    /**
     * Format date
     *
     * @param \DateTime $date
     * @param bool $ignoreTZ
     * @return string
     */
    public static function date($date, $ignoreTZ = false)
    {
        return Timezone::on($date, $ignoreTZ ? 'UTC' : null)->format('j F, Y');
    }

    /**
     * Format date, short form
     *
     * @param \DateTime $date
     * @param bool $ignoreTZ
     * @return string
     */
    public static function shortDate($date, $ignoreTZ = false)
    {
        return Timezone::on($date, $ignoreTZ ? 'UTC' : null)->format('j M, Y');
    }

    /**
     * Format any order (purchase/sale)
     *
     * @param BaseOrder $order
     * @return string
     */
    public static function order(BaseOrder $order)
    {
        return sprintf("%s #%s", $order->type->label(), $order->id);
    }

    /**
     * Format time
     *
     * @param \DateTime $date
     * @return string
     */
    public static function time($date)
    {
        return Settings::get('general.time_format') === SettingsManager::TIME_FORMAT_12H
            ? Timezone::on($date)->format('h:i A')
            : Timezone::on($date)->format('H:i');
    }

    /**
     * Format datetime
     *
     * @param \DateTime $date
     * @return string
     */
    public static function datetime($date, $hideMidnightTime = false)
    {
        $format = Settings::get('general.time_format') === SettingsManager::TIME_FORMAT_12H
            ? 'h:i A, j F Y'
            : 'H:i, j F Y';

        $date = Timezone::on($date);

        if ($hideMidnightTime && $date->format('H:i') === '00:00') {
            $format = 'j F Y';
        }

        return $date->format($format);
    }

    /**
     * Format duration
     *
     * @param int $minutes
     * @return string
     */
    public static function duration($minutes)
    {
        return sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
    }

    public static function hoursToTime($hours)
    {
        return Carbon::createFromTime($hours, 0, 0)->format('g A');
    }

    /**
     * Format time interval
     *
     * @param \DateTime $date
     * @param int $interval
     * @return string
     */
    public static function interval($date, $interval, $join = ' - ')
    {
        if (! $interval) {
            return static::time($date);
        }

        $end = clone $date;
        $end->addMinutes($interval);

        $timeStart = static::time($date);
        $timeEnd = static::time($end);

        if ($timeStart === $timeEnd) {
            return $timeStart;
        }

        return $timeStart . $join . $timeEnd;
    }

    /**
     * Format product
     *
     * @param mixed $product
     * @param bool $appendProps
     * @param string $context
     * @return string
     */
    public static function product($product, $appendProps = true, $context = null, $secondaryName = false)
    {
        return self::formatProduct($product, $appendProps, $context, function ($name, $props) {
            return $name
                . ($props->count() !== 0
                    ? (' '. $props->pluck('title')->join(' '))
                    : '');
        }, $secondaryName);
    }

    public static function address($address)
    {
        if (!$address) {
            return '';
        }

        return $address->address . ', ' . $address->city . ', ' . $address->postalcode;
    }

    /**
     * Format product as HTML
     *
     * @param $product
     * @param $appendProps
     * @param string $context
     * @param bool $secondaryName
     * @param bool $symbolsAsImages
     * @return string
     */
    public static function productHtml($product, $appendProps = true, $context = null, $secondaryName = false, $symbolsAsImages = false)
    {
        return self::formatProduct($product, $appendProps, $context, function($name, $props) use($symbolsAsImages) {
            return htmlspecialchars($name)
                . ($props->count() !== 0
                    ? (' '. $props->map(function($value) use($symbolsAsImages) {
                        return $value->is_symbol
                            ? static::formatSymbols($value, $symbolsAsImages)
                            : htmlspecialchars($value->title);
                    })->join(' '))
                    : '');
        }, $secondaryName);
    }

    /**
     * Format phone number
     *
     * @param string $number
     * @return string
     */
    public static function phone($number)
    {
        return preg_replace("/^[+]?([0-9]*)([0-9]{4})([0-9]{4})$/", "(\\1) \\2 \\3", $number);
    }

    /**
     * Format money
     *
     * @param float $amount
     * @param string $currency
     * @param int $minDecimals
     * @param int $maxDecimals
     * @return string
     */
    public static function money($amount, $currency = null, $minDecimals = 2, $maxDecimals = 2)
    {
        $currency = $currency ?: Settings::get('general.currency');
        $dollars = ['usd', 'aud', 'cad', 'nzd', 'sgd', 'hkd', 'twd', 'usdt'];
        $sign = $amount < 0 ? '-' : '';

        if (in_array(strtolower($currency), $dollars)) {
            $prefix = '$';
            $suffix = '';
        } else {
            $prefix = '';
            $suffix = ' ' . strtoupper($currency);
        }

        $formatted = number_format(abs($amount), $maxDecimals);

        if ($maxDecimals > $minDecimals && $maxDecimals > 0) {
            [$whole, $decimal] = explode('.', $formatted);
            $formatted = $whole . '.' . str_pad(rtrim($decimal, '0'), $minDecimals, '0', STR_PAD_RIGHT);
        }

        return $sign . $prefix . $formatted . $suffix;
    }

    /**
     * Format number to 2 decimal places
     *
     * @param float|int $a
     * @return string
     */
    public static function numberFormat($a)
    {
        return number_format((float)$a, Amount::PRECISION, '.', '');
    }

    /**
     * Format product protected
     *
     * @param $product
     * @param $appendProps
     * @param $context
     * @param $formatter
     * @return mixed|string
     */
    protected static function formatProduct($product, $appendProps, $context, $formatter, $secondaryName = false)
    {
        if ($product instanceof BaseOrderItem) {
            if ($product->hasProduct()) {
                $product = $product->product;
            } else {
                return $formatter($product->name, collect([]));
            }
        }

        if ($product->type->value() === ProductType::VARIANT) {
            return $formatter(
                self::getProductName($product->parent, $secondaryName),
                $appendProps
                    ? static::filterPropertyValues($product->combined_property_values, $context)
                    : collect([])
            );
        } elseif ($product->type->value() === ProductType::SIMPLE) {
            return $formatter(
                self::getProductName($product, $secondaryName),
                $appendProps
                    ? static::filterPropertyValues($product->combined_property_values, $context)
                    : collect([])
            );
        }

        return $formatter(self::getProductName($product, $secondaryName), collect([]));
    }

    /**
     * Filter property values by context
     *
     * @param \Illuminate\Support\Collection $propertyValues
     * @param string $context
     * @return \Illuminate\Support\Collection
     */
    protected static function filterPropertyValues($propertyValues, $context)
    {
        return $propertyValues->filter(function($value) use($context) {
            return match($context) {
                self::CONTEXT_PREPARATION => ! $value->is_hidden_in_preparation,
                self::CONTEXT_ORDER_QTY => ! $value->is_hidden_in_order_qty_report,
                self::CONTEXT_PRODUCT_QTY => ! $value->is_hidden_in_product_qty_report,
                default => true,
            };
        });
    }

    /**
     * Format symbols
     *
     * @param \App\Products\Models\PropertyValue $value
     * @param bool $symbolsAsImages
     * @return string
     */
    protected static function formatSymbols($value, $symbolsAsImages)
    {
        $symbols = (array) $value->getConfig('symbol');

        if (empty($symbols)) {
            return '';
        }

        return implode(' ', array_map(function($symbol) use($symbolsAsImages) {
            return $symbolsAsImages
                ? sprintf('<img src="data:image/jpeg;base64,%s" alt="%s" />', Symbol::toJpg($symbol), $symbol)
                : Symbol::toHtml($symbol);
        }, $symbols));
    }

    /**
     * Get product name
     *
     * @param $product
     * @param bool $secondaryName
     *
     * @return mixed|string
     */
    protected static function getProductName($product, bool $secondaryName = false)
    {
        $name = $product->name;

        if ($secondaryName && $product->ext_title) {
            $name = $product->ext_title;
        }

        return $name;
    }
}
