<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Response;

class ResponeTimeComponentTest extends BaseTestCase
{
    public function testResponseTime()
    {
        $request = request();
        $response = new Response('test content', 212);
        event(new RequestHandled($request, $response));

        $metrics = $this->datastub->getMetric('timing', 'airslate.response_time');


        $this->assertEquals(
            ['code' => $response->getStatusCode(), 'method' => $request->method()],
            $metrics[0]['tags']
        );

        $this->assertTrue($metrics[0]['value'] < microtime(true));
    }
}
