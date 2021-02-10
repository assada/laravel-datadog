<?php

declare(strict_types=1);

namespace AirSlate\Tests\Stub;

use AirSlate\Datadog\Events\DatadogEventExtendedInterface;

class ExtendedCustomEvent extends CustomEvent implements DatadogEventExtendedInterface
{
    public function getValue(): int
    {
        return $this->data['value'];
    }

    public function getMetricType(): string
    {
        return $this->data['metric_type'];
    }
}
