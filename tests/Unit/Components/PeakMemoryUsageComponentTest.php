<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Response;

class PeakMemoryUsageComponentTest extends BaseTestCase
{
    public function testPeakMemory()
    {
        $request = request();
        $response = new Response('test content', 212);
        event(new RequestHandled($request, $response));
        $metrics = $this->datastub->getMetric(
            'gauge',
            "airslate.memory_peak_usage"
        );

        $this->assertEquals(
            ['code' => $response->getStatusCode(), 'method' => $request->method()],
            $metrics[0]['tags']
        );

        $this->assertTrue($metrics[0]['value'] > 0);
    }
}
