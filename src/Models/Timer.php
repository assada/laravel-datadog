<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Models;

use AirSlate\Datadog\Exceptions\IntervalException;

class Timer
{
    /** @var float */
    private $start = 0;

    /** @var float */
    private $stop = 0;

    public function start(): void
    {
        $this->start = microtime(true);
    }

    public function stop(): void
    {
        if (!$this->start) {
            new IntervalException('You must start interval before stopping');
        }
        $this->stop = microtime(true);
    }

    public function getInteval(): float
    {
        if (!$this->stop || !$this->start) {
            new IntervalException('You must start and stop timer before getting interval');
        }

        return $this->stop - $this->start;
    }
}
