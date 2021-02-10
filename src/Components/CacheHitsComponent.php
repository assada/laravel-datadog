<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;

class CacheHitsComponent extends ComponentAbstract
{
    public function register(): void
    {
        $this->listen(CacheHit::class, function (): void {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'hit',
            ]);
        });

        $this->listen(CacheMissed::class, function (): void {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'miss',
            ]);
        });

        $this->listen(KeyForgotten::class, function (): void {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'del',
            ]);
        });

        $this->listen(KeyWritten::class, function (): void {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'put',
            ]);
        });
    }
}
