<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\CacheHitsComponent;
use AirSlate\Datadog\Services\Datadog;
use AirSlate\Tests\Stub\DatadogStub;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;

class CacheHitsComponentTest extends BaseTestCase
{

    /**
     * @test
     */
    public function subscribeForEvents()
    {
        event(new CacheHit('test_hit', 'test_hit_value', ['test_hit_tag']));
        event(new CacheMissed('test_missed', ['test_missed_tag']));
        event(new KeyForgotten('test_forgotten', ['test_forgotten_tag']));
        event(new KeyWritten('test_written','test_written_value', null, ['test_written_tag']));

        $data = $this->datastub->getIncrements("airslate.cache.item");

        $this->assertIncremens($data[0], 'hit');
        $this->assertIncremens($data[1], 'miss');
        $this->assertIncremens($data[2], 'del');
        $this->assertIncremens($data[3], 'put');
    }

    /**
     * @param $data
     */
    private function assertIncremens(&$data, $tag): void
    {
        $this->assertEquals(1, $data['sample_rate']);
        $this->assertEquals($tag, $data['tags']['status']);
        $this->assertEquals(1, $data['value']);
    }
}
