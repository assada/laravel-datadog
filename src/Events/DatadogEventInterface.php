<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Events;

interface DatadogEventInterface
{
    /**
     * Event category like db/job/cache etc...
     * @return string
     */
    public function getEventCategory(): string;

    /**
     * Event name may be something lke transaction/query/item etc...
     * @return string
     */
    public function getEventName(): string;

    /**
     * For example: ['tagNeme1' => 'tagValue1', 'tagName2' => 'tagValue2']
     * @return array<string, string>
     */
    public function getTags(): array;
}
