<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Models;

/**
 * Class Counter
 * @package AirSlate\Datadog\Models
 */
class Counter
{
    /**
     * @var int
     */
    private $value = 0;

    /**
     *
     */
    public function increment(): void
    {
        $this->value++;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
