<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use AirSlate\Datadog\Exceptions\CounterException;
use AirSlate\Datadog\Models\Counter;

class CounterManager
{
    private $counters = [];

    public function startCounter(string $name): Counter
    {
        return $this->counters[$name] = new Counter();
    }

    /**
     * @param string $name
     *
     * @throws CounterException
     */
    public function incrementCounter(string $name): void
    {
        if (isset($this->counters[$name])) {
            $this->getCounter($name)->increment();
        }
    }

    /**
     * @return int
     *
     * @throws CounterException
     */
    public function getValue(string $name)
    {
        return $this->getCounter($name)->getValue();
    }

    /**
     * @param string $name
     *
     * @return Counter
     *
     * @throws CounterException
     */
    public function getCounter(string $name): Counter
    {
        if (!isset($this->counters[$name])) {
            throw new CounterException("Counter hasn't started");
        }

        return $this->counters[$name];
    }

    public function clearCounter(string $name)
    {
        unset($this->counters[$name]);
    }
}
