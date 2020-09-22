<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit;

use AirSlate\Datadog\Services\Datadog;
use AirSlate\Tests\Stub\DatadogStub;
use AirSlate\Tests\Unit\Components\JobQueryCounterComponentTest;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Queue\Jobs\RedisJob;

class BaseTestCase extends TestCase
{
    /**
     * @var DatadogStub
     */
    protected $datastub;

    public function createApplication()
    {
        define('LARAVEL_START', 0);

        $application = new Application(
            dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'tests'
            . DIRECTORY_SEPARATOR . 'app'
        );
        $application->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Illuminate\Foundation\Http\Kernel::class
        );

        $application->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Illuminate\Foundation\Console\Kernel::class
        );

        $application->singleton(Datadog::class , function () {
            return $this->datastub = new DatadogStub();
        });

        $application->make(\Illuminate\Foundation\Console\Kernel::class)->bootstrap();


        return $application;
    }

    protected function createJobMock(): RedisJob
    {
        $jobMock = $this->createMock(RedisJob::class);
        $jobMock->method('resolveName')->willReturn('redisJob');
        $jobMock->method('getQueue')->willReturn('test_queue');
        return $jobMock;
    }

    protected function createConnectionMock(): Connection
    {
        $mock = $this->createMock(Connection::class);
        $mock->method('getName')->willReturn('test_connection');
        return $mock;
    }

    protected function riseEventQueryExecuted(): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getName')->willReturn('test_connection');

        event(new QueryExecuted(
            'select * from test_table',
            ['test' => 1],
            10,
            $connectionMock
        ));
    }
}
