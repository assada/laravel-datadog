<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\CacheHitsComponent;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;

class CacheHitsComponentTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(CacheHitsComponent::class)->register();
    }

    public function testSubscribeForCacheHit(): void
    {
        event(new CacheHit('test_hit', 'test_hit_value', ['test_hit_tag']));

        $data = $this->datastub->getIncrements('airslate.cache.item');

        self::assertEquals(1, $data[0]['sample_rate']);
        self::assertEquals('hit', $data[0]['tags']['status']);
        self::assertEquals(1, $data[0]['value']);
    }

    public function testSubscribeForMiss(): void
    {
        event(new CacheMissed('test_missed', ['test_missed_tag']));

        $data = $this->datastub->getIncrements("airslate.cache.item");

        self::assertEquals(1, $data[0]['sample_rate']);
        self::assertEquals('miss', $data[0]['tags']['status']);
        self::assertEquals(1, $data[0]['value']);
    }

    public function testSubscribeForDel(): void
    {
        event(new KeyForgotten('test_forgotten', ['test_forgotten_tag']));

        $data = $this->datastub->getIncrements("airslate.cache.item");

        self::assertEquals(1, $data[0]['sample_rate']);
        self::assertEquals('del', $data[0]['tags']['status']);
        self::assertEquals(1, $data[0]['value']);
    }

    public function testSubscribeForPut(): void
    {
        event(new KeyWritten('test_written', 'test_written_value', null, ['test_written_tag']));

        $data = $this->datastub->getIncrements("airslate.cache.item");

        self::assertEquals(1, $data[0]['sample_rate']);
        self::assertEquals('put', $data[0]['tags']['status']);
        self::assertEquals(1, $data[0]['value']);
    }
}
