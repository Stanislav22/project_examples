<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\LazyCollection;

class ReportableIterator
{
    /**
     * Make iterator instance
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  callable $callback
     * @param  int $chunkSize
     * @return \Illuminate\Support\LazyCollection
     */
    public static function make($query, $callback, $chunkSize = 100)
    {
        if ($query instanceof Builder) {
            $total = $query->toBase()->getCountForPagination();
        } else {
            $total = $query->getCountForPagination();
        }
        $done = 0;

        return LazyCollection::make(function() use($query, $chunkSize, $callback, $total, &$done) {
            $page = 1;

            while (true) {
                $results = $query->forPage($page++, $chunkSize)->get();

                foreach ($results as $result) {
                    yield $result;

                    $callback(++$done, $total);
                }

                if ($results->count() < $chunkSize) {
                    return;
                }
            }
        });
    }

    /**
     * Make iterator instance with throttling
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  callable $callback
     * @param  int $chunkSize
     * @param  int $delay
     * @return \Illuminate\Support\LazyCollection
     */
    public static function throttle($query, $callback, $chunkSize = 100, $delay = 5)
    {
        $progress = new Throttle($callback, $delay);

        return static::make($query, function($done, $total) use($progress) {
            $progress($done, $total);
        }, $chunkSize);
    }
}
