<?php

namespace AirSlate\Datadog\Services;

use DataDog\DogStatsd;

class Datadog extends DogStatsd
{
    private $tags = [];

    /**
     * {@inheritdoc}
     */
    public function send($data, $sampleRate = 1.0, $tags = null): void
    {
        $tags = $this->prepareTags($tags);
        parent::send($data, $sampleRate, $tags);
    }

    public function addTag(string $key, $value): void
    {
        if (!empty($key) && !empty($value)) {
            $this->tags[$key] = $value;
        }
    }

    private function prepareTags(?array $tags): array
    {
        return array_merge($this->tags, $tags);
    }
}
