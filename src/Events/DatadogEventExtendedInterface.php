<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Events;

interface DatadogEventExtendedInterface extends DatadogEventInterface
{
    public const METRIC_TYPE_HISTOGRAM = 'histogram';
    public const METRIC_TYPE_INCREMENT = 'increment';
    public const METRIC_TYPE_DECREMENT = 'decrement';
    public const METRIC_TYPE_GAUGE = 'gauge';
    public const METRIC_TYPE_TIMING = 'timing';

    /**
     * Value for your metric, return 1 as default.
     * @return int
     */
    public function getValue(): int;

    /**
     * metric type you can see in constants of this interface
     * @return string
     */
    public function getMetricType(): string;
}
