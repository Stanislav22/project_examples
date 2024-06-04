<?php

namespace App\Helpers;

use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Builder;

class ChunkedCursor
{
    public static function make(Builder $query, $chunkSize = 100)
    {
        return LazyCollection::make(function() use($query, $chunkSize) {
            $accumulator = [];
            $cursor = $query->cursor();

            foreach ($cursor as $item) {
                $accumulator[] = $item;

                if (count($accumulator) >= $chunkSize) {
                    $query->eagerLoadRelations($accumulator);

                    foreach ($accumulator as $item) {
                        yield $item;
                    }
                    
                    $accumulator = [];
                }
            }

            if (count($accumulator) > 0) {
                $query->eagerLoadRelations($accumulator);

                foreach ($accumulator as $item) {
                    yield $item;
                }
            }
        });
    }
}