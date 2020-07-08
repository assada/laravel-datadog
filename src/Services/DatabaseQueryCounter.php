<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Services;

/**
 * Class DatabaseQueryCounter
 *
 * @package AirSlate\Datadog\Services
 */
class DatabaseQueryCounter
{
    private $queryCount = 0;

    public function flush(): void
    {
        $this->queryCount = 0;
    }

    public function increment(): void
    {
        ++$this->queryCount;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->queryCount;
    }
}
