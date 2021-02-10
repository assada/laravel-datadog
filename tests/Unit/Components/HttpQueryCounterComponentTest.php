<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\HttpQueryCounterComponent;
use AirSlate\Tests\InteractsWithQueryExecuted;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpQueryCounterComponentTest extends BaseTestCase
{
    use InteractsWithQueryExecuted;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(HttpQueryCounterComponent::class)->register();
    }

    public function createApplication(): Application
    {
        putenv('APP_RUNNING_IN_CONSOLE=false');

        return parent::createApplication();
    }

    public function testSuccess(): void
    {
        $this->riseQueryExecutedEvent();

        $request = new Request();
        $response = new Response('test content', 212);

        event(new RequestHandled($request, $response));

        $metrics = $this->datastub->getMetric('gauge', 'airslate.db.queries');

        self::assertEquals(
            ['code' => $response->getStatusCode(), 'method' => $request->method()],
            $metrics[0]['tags']
        );

        self::assertEquals(1, $metrics[0]['value']);
    }
}
