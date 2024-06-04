<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use KitLoong\AppLogger\QueryLog\LogWriter as QueryLogger;
use Illuminate\Support\Facades\Log;

class Profiler extends QueryLogger
{
    /**
     * @var array
     */
    protected static $timers = [];

    /**
     * @var bool
     */
    public static $logQueries = false;

    /**
     * @var int
     */
    public static $slowQueryThreshold = 0;

    /**
     * @var int
     */
    public static $frequentQueryThreshold = 0;

    /**
     * @inheritdoc
     */
    public function handle($request, Closure $next)
    {
        self::$timers = [];

        $queries = new \ArrayObject();
        $stats = new \ArrayObject([
            'queries' => 0,
            'queries_ms' => 0,
        ]);

        $start = microtime(true);

        DB::listen(function (QueryExecuted $query) use($stats, $queries) {
            $time = $query->time;
            $stats['queries']++;
            $stats['queries_ms'] += $time;

            if (static::$logQueries) {
                $sql = $query->sql;
                $key = md5($sql);

                if (! isset($queries[$key])) {
                    $queries[$key] = [
                        'sql' => $sql,
                        'time' => $time,
                        'count' => 1,
                    ];
                } else {
                    $queries[$key]['time'] += $time;
                    $queries[$key]['count']++;
                }
            }
        });

        $response = $next($request);
        $stats['response_ms'] = round((microtime(true) - $start) * 1000, 2);

        foreach (self::$timers as $timer => $time) {
            $total = $time['total'];

            if (isset($time['start'])) {
                $total += microtime(true) - $time['start'];
            }

            $stats[$timer .'_ms'] = round($total * 1000, 2);
        }

        $response->headers->set('X-Profiler', http_build_query((array) $stats, '', '; '));

        if (static::$logQueries) {
            foreach ($queries as $query) {
                if ($query['time'] >= static::$slowQueryThreshold || $query['count'] >= static::$frequentQueryThreshold) {
                    Log::debug(sprintf('[X-Profiler] Query (T = %0.2f, C = %d): %s', $query['time'], $query['count'], $query['sql']));
                }
            }
        }

        return $response;
    }

    /**
     * Start a timer
     *
     * @param string $name
     */
    public static function startTimer($name)
    {
        if (! isset(self::$timers[$name])) {
            self::$timers[$name] = [
                'start' => microtime(true),
                'total' => 0,
            ];
        } elseif (! isset(self::$timers[$name]['start'])) {
            self::$timers[$name]['start'] = microtime(true);
        }
    }

    /**
     * End timer
     *
     * @param string $name
     */
    public static function stopTimer($name)
    {
        if (isset(self::$timers[$name]['start'])) {
            self::$timers[$name]['total'] += microtime(true) - self::$timers[$name]['start'];
            unset(self::$timers[$name]['start']);
        }
    }
}
