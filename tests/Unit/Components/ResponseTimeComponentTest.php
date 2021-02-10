<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\ResponseTimeComponent;
use AirSlate\Datadog\Exceptions\ComponentRegistrationException;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResponseTimeComponentTest extends BaseTestCase
{
    /**
     * @throws ComponentRegistrationException
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', 0);
        }

        $this->app->make(ResponseTimeComponent::class)->register();
    }

    public function testResponseTime(): void
    {
        $request = new Request();
        $response = new Response('test content', 212);

        event(new RequestHandled($request, $response));

        $metrics = $this->datastub->getMetric('timing', 'airslate.response_time');

        self::assertEquals(
            ['code' => $response->getStatusCode(), 'method' => $request->method()],
            $metrics[0]['tags']
        );

        self::assertTrue($metrics[0]['value'] < microtime(true));
    }
}
