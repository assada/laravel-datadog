<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use AirSlate\Datadog\Events\DatadogEventExtendedInterface;
use AirSlate\Datadog\Events\DatadogEventInterface;
use DomainException;

class CustomEventsComponent extends ComponentAbstract
{
    public function register(): void
    {
        /** @var string $eventName */
        $this->listen('*', function (string $eventName, array $data) {
            $event = $data[0];

            if (!$event instanceof DatadogEventInterface) {
                return;
            }

            $stats = $this->getStat("{$event->getEventCategory()}.{$event->getEventName()}");

            if ($event instanceof DatadogEventExtendedInterface) {
                $this->sendExtendedMetric($event, $stats);
            } else {
                $this->statsd->increment($stats, 1, $event->getTags(), 1);
            }
        });
    }

    /**
     * @param DatadogEventExtendedInterface $event
     * @param string $stats
     */
    private function sendExtendedMetric(DatadogEventExtendedInterface $event, string $stats): void
    {
        switch ($event->getMetricType()) {
            case DatadogEventExtendedInterface::METRIC_TYPE_INCREMENT:
                $this->statsd->increment($stats, 1, $event->getTags(), $event->getValue());
                break;
            case DatadogEventExtendedInterface::METRIC_TYPE_DECREMENT:
                $this->statsd->decrement($stats, 1, $event->getTags(), $event->getValue());
                break;
            case DatadogEventExtendedInterface::METRIC_TYPE_HISTOGRAM:
                $this->statsd->histogram($stats, $event->getValue(), 1, $event->getTags());
                break;
            case DatadogEventExtendedInterface::METRIC_TYPE_GAUGE:
                $this->statsd->gauge($stats, $event->getValue(), 1, $event->getTags());
                break;
            case DatadogEventExtendedInterface::METRIC_TYPE_TIMING:
                $this->statsd->timing($stats, $event->getValue(), 1, $event->getTags());
                break;
            default:
                throw new DomainException('metric' . $event->getMetricType() . ' is not found in ' .
                    DatadogEventExtendedInterface::class . ' allowed metrics');
        }
    }
}
