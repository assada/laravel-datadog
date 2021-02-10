<?php

declare(strict_types=1);

namespace AirSlate\Tests\Stub;

use AirSlate\Datadog\Events\DatadogEventInterface;

class CustomEvent implements DatadogEventInterface
{
    /** @var array */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getEventCategory(): string
    {
        return $this->data['event_category'];
    }

    public function getEventName(): string
    {
        return $this->data['event_name'];
    }

    /**
     * @return array<string, string>
     */
    public function getTags(): array
    {
        return [
            'tag_key' => 'tag_value'
        ];
    }
}
