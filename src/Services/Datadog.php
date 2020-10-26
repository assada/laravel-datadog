<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use DataDog\DogStatsd;
use Illuminate\Support\Facades\Config;

/**
 * Class Datadog
 *
 * @package AirSlate\Datadog\Services
 */
class Datadog extends DogStatsd
{
    /** @var array<string, string> */
    private $tags = [];

    /**
     * {@inheritdoc}
     */
    public function send($data, $sampleRate = 1.0, $tags = null): void
    {
        $tags = $this->prepareTags(is_array($tags) ? $tags : null);
        parent::send($data, $sampleRate, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function timing($stat, $time, $sampleRate = 1.0, $tags = null): void
    {
        parent::timing($stat, $time, $sampleRate, $tags);

        if (Config::get('datadog.is_send_increment_metric_with_timing_metric')) {
            $this->increment($stat, $sampleRate, $tags);
        }
    }

    public function addTag(string $key, string $value): void
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
