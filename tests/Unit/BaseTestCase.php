<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit;

use AirSlate\Datadog\ServiceProviders\DatadogProvider;
use AirSlate\Datadog\Services\Datadog;
use AirSlate\Tests\Stub\DatadogStub;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    protected DatadogStub $datastub;

    public function setUp(): void
    {
        parent::setUp();

        $this->swap(Datadog::class, new DatadogStub());

        $this->datastub = $this->app->make(Datadog::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            DatadogProvider::class,
        ];
    }
}
