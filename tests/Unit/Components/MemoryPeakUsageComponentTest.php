<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\MemoryPeakUsageComponent;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MemoryPeakUsageComponentTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(MemoryPeakUsageComponent::class)->register();
    }

    public function testPeakMemory(): void
    {
        $request = new Request();
        $response = new Response('test content', 212);

        event(new RequestHandled($request, $response));

        $metrics = $this->datastub->getMetric(
            'gauge',
            'airslate.memory_peak_usage'
        );

        self::assertEquals(
            ['code' => 212, 'method' => $request->method()],
            $metrics[0]['tags']
        );

        self::assertTrue($metrics[0]['value'] > 0);
    }
}
