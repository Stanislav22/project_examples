<?php

namespace App\Helpers;

class Throttle
{
    /**
     * @var  int
     */
    protected $delay;

    /**
     * @var  int
     */
    protected $time;

    /**
     * @var  Closure
     */
    protected $callback;

    /**
     * @param  int $delay
     * @param  Closure $callback
     */
    public function __construct(\Closure $callback, $delay = 5)
    {
        $this->delay = $delay;
        $this->time = time();
        $this->callback = $callback;
    }

    /**
     * @return  void
     */
    public function __invoke()
    {
        $time = time();

        if ($time - $this->time >= $this->delay) {
            $this->time = $time;
            $this->callback->__invoke(...func_get_args());
        }
    }
}