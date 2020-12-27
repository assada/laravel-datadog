<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;

/**
 * Class CacheHitsComponent
 * @package AirSlate\Datadog\Components
 */
class CacheHitsComponent extends ComponentAbstract
{
    public function register(): void
    {
        $this->listen(CacheHit::class, function (CacheHit $cacheHit) {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'hit',
            ]);
        });

        $this->listen(CacheMissed::class, function (CacheMissed $cacheMissed) {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'miss',
            ]);
        });

        $this->listen(KeyForgotten::class, function (KeyForgotten $keyForgotten) {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'del',
            ]);
        });

        $this->listen(KeyWritten::class, function (KeyWritten $keyWritten) {
            $this->statsd->increment($this->getStat('cache.item'), 1, [
                'status' => 'put',
            ]);
        });
    }
}
