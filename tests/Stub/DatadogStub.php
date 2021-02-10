<?php

declare(strict_types=1);

namespace AirSlate\Tests\Stub;

use AirSlate\Datadog\Events\DatadogEventExtendedInterface;
use AirSlate\Datadog\Services\Datadog;

/**
 * Class DatadogStub
 * @package AirSlate\Tests\Stub
 */
class DatadogStub extends Datadog
{
    /** @var array  */
    private $increments = [];

    /** @var array  */
    private $timings = [];

    /** @var array  */
    private $gauges = [];

    /** @var array  */
    private $data = [];

    /**
     * @param array|string $stats
     * @param float $sampleRate
     * @param null $tags
     * @param int $value
     * @return bool|void
     */
    public function increment($stats, $sampleRate = 1.0, $tags = null, $value = 1)
    {
        $this->setStat(
            DatadogEventExtendedInterface::METRIC_TYPE_INCREMENT,
            $stats,
            $value,
            $sampleRate,
            $tags
        );
    }

    /**
     * @param string $stat
     * @param float $time
     * @param float $sampleRate
     * @param null $tags
     */
    public function timing($stat, $time, $sampleRate = 1.0, $tags = null): void
    {
        $this->setStat(
            DatadogEventExtendedInterface::METRIC_TYPE_TIMING,
            $stat,
            $time,
            $sampleRate,
            $tags
        );
    }

    /**
     * @param string $stat
     * @param float $value
     * @param float $sampleRate
     * @param null $tags
     */
    public function histogram($stat, $value, $sampleRate = 1.0, $tags = null)
    {
        $this->setStat(
            DatadogEventExtendedInterface::METRIC_TYPE_HISTOGRAM,
            $stat,
            $value,
            $sampleRate,
            $tags
        );
    }

    /**
     * @param array|string $stats
     * @param float $sampleRate
     * @param null $tags
     * @param int $value
     */
    public function decrement($stats, $sampleRate = 1.0, $tags = null, $value = -1): void
    {
        $this->setStat(
            DatadogEventExtendedInterface::METRIC_TYPE_DECREMENT,
            $stats,
            $value,
            $sampleRate,
            $tags
        );
    }

    /**
     * @param string $stat
     * @param float $value
     * @param float $sampleRate
     * @param null $tags
     */
    public function gauge($stat, $value, $sampleRate = 1.0, $tags = null): void
    {
        $this->setStat(
            DatadogEventExtendedInterface::METRIC_TYPE_GAUGE,
            $stat,
            $value,
            $sampleRate,
            $tags
        );
    }

    /**
     * @param string|null $stats
     *
     * @return array|null
     */
    public function getIncrements(string $stats = null): ?array
    {
        if ($stats) {
            return $this->data[DatadogEventExtendedInterface::METRIC_TYPE_INCREMENT][$stats] ?? null;
        }
        return $this->increments;
    }

    /**
     * @param string|null $stat
     *
     * @return array|null
     */
    public function getTimings(string $stat = null): ?array
    {
        if ($stat) {
            return $this->data[DatadogEventExtendedInterface::METRIC_TYPE_TIMING][$stat] ?? null;
        }

        return $this->timings;
    }

    /**
     * @param string|null $stat
     * @return array|null
     */
    public function getGauges(string $stat = null): ?array
    {
        if ($stat) {
            return $this->data[DatadogEventExtendedInterface::METRIC_TYPE_GAUGE][$stat] ?? null;
        }
        return $this->gauges;
    }

    /**
     * @param string $merticType
     * @param string $metricName
     */
    public function getMetric(string $merticType, string $metricName)
    {
        return $this->data[$merticType][$metricName];
    }

    /**
     * @param string $type
     * @param string $stat
     * @param mixed $value
     * @param float $sampleRate
     * @param $tags
     */
    private function setStat(string $type, string $stat, $value, float $sampleRate, $tags): void
    {
        $this->data[$type][$stat][] = [
            'stat' => $stat,
            'value' => $value,
            'sample_rate' => $sampleRate,
            'tags' => $tags
        ];
    }
}
