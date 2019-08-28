<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use DataDog\DogStatsd;

/**
 * Class Datadog
 *
 * @package AirSlate\Datadog\Services
 */
class Datadog extends DogStatsd
{
    private $tags = [];

    /**
     * {@inheritdoc}
     */
    public function send($data, $sampleRate = 1.0, $tags = null): void
    {
        $tags = $this->prepareTags(is_array($tags) ? $tags : null);
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
        $tags = is_array($tags) ? $tags : [];

        return array_merge($this->tags, $tags);
    }
}
