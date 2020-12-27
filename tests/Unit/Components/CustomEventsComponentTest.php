<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Events\DatadogEventExtendedInterface;
use AirSlate\Tests\Stub\CustomEvent;
use AirSlate\Tests\Stub\ExtendedCustomEvent;
use AirSlate\Tests\Unit\BaseTestCase;

class CustomEventsComponentTest extends BaseTestCase
{
    /**
     * @test
     */
    public function checkCustomEvent()
    {
        $customEvent = new CustomEvent([
            'event_category' => 'event.category',
            'event_name' => 'event.name',
        ]);
        event($customEvent);
        $increments = $this->datastub->getIncrements(
            "airslate.{$customEvent->getEventCategory()}.{$customEvent->getEventName()}"
        );
        $this->assertTrue(isset($increments[0]));
        $this->assertEquals($customEvent->getTags(), $increments[0]['tags']);
    }

    /**
     * @test
     * @dataProvider extendedEvents
     */
    public function checkExtendedCustomEvent(DatadogEventExtendedInterface $customEvent)
    {
        event($customEvent);
        $metric = $this->datastub->getMetric(
            $customEvent->getMetricType(),
            "airslate.{$customEvent->getEventCategory()}.{$customEvent->getEventName()}"
        );

        $this->assertTrue(isset($metric[0]));
        $this->assertEquals($customEvent->getTags(), $metric[0]['tags']);

        $this->assertEquals(
            $customEvent->getValue(),
            $metric[0]['value']
        );
    }

    public function extendedEvents()
    {
        return [
            [
                new ExtendedCustomEvent([
                    'event_category' => 'event.category',
                    'event_name' => 'event.name',
                    'value' => 19,
                    'metric_type' => DatadogEventExtendedInterface::METRIC_TYPE_GAUGE
                ])
            ],
            [
                new ExtendedCustomEvent([
                    'event_category' => 'event.category',
                    'event_name' => 'event.name',
                    'value' => 19,
                    'metric_type' => DatadogEventExtendedInterface::METRIC_TYPE_INCREMENT
                ])
            ],
            [
                new ExtendedCustomEvent([
                    'event_category' => 'event.category',
                    'event_name' => 'event.name',
                    'value' => 19,
                    'metric_type' => DatadogEventExtendedInterface::METRIC_TYPE_TIMING
                ])
            ],
            [
                new ExtendedCustomEvent([
                    'event_category' => 'event.category',
                    'event_name' => 'event.name',
                    'value' => 19,
                    'metric_type' => DatadogEventExtendedInterface::METRIC_TYPE_DECREMENT
                ])
            ],
            [
                new ExtendedCustomEvent([
                    'event_category' => 'event.category',
                    'event_name' => 'event.name',
                    'value' => 19,
                    'metric_type' => DatadogEventExtendedInterface::METRIC_TYPE_HISTOGRAM
                ])
            ]
        ];
    }
}
