<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use AirSlate\Datadog\Exceptions\IntervalException;
use AirSlate\Datadog\Models\Timer;

class TimerManager
{
    /** @var array  */
    private $objects = [];

    public function startTimer(string $name, string $key = ''): void
    {
        $timer = new Timer();
        $timer->start();
        $this->objects[$this->getKey($name, $key)] = $timer;
    }

    public function getTimer(string $name, string $key = ''): Timer
    {
        $timer = $this->objects[$this->getKey($name, $key)];
        if (!$timer) {
            throw new IntervalException("Interval doesn't exists");
        }
        return $timer;
    }

    public function stopTimer(string $name, string $key = ''): Timer
    {
        $timer = $this->getTimer($name, $key);
        $timer->stop();
        return $timer;
    }

    /**
     * @param string $name
     * @param string $key
     * @return string
     */
    private function getKey(string $name, string $key): string
    {
        return 'timer_' . $name . '_' . $key;
    }
}
