<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\JobQueryCounterComponent;
use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Tests\InteractsWithQueryExecuted;
use AirSlate\Tests\Unit\BaseTestCase;
use Exception;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\RedisJob;

class JobQueryCounterComponentTest extends BaseTestCase
{
    use InteractsWithQueryExecuted;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(JobQueryCounterComponent::class)->register();
    }

    /**
     * @dataProvider provideEvents
     *
     * @param $jobStarted
     * @param $jobEnded
     * @param $tags
     */
    public function testSuccess($jobStarted, $jobEnded, $tags): void
    {
        event($jobStarted);
        $this->riseQueryExecutedEvent();
        event($jobEnded);
        $gauges = $this->datastub->getGauges('airslate.queue.db.queries');

        self::assertCount(1, $gauges);
        self::assertEquals($tags, $gauges[0]['tags']);
        self::assertEquals(1, $gauges[0]['value']);
    }

    /**
     * @dataProvider provideEvents
     *
     * @param $jobStarted
     * @param $jobEnded
     * @param $tags
     */
    public function testTwoCalls($jobStarted, $jobEnded, $tags): void
    {
        event($jobStarted);
        $this->riseQueryExecutedEvent();
        event($jobEnded);
        event($jobStarted);
        $this->riseQueryExecutedEvent();
        $this->riseQueryExecutedEvent();
        event($jobEnded);

        $gauges = $this->datastub->getGauges('airslate.queue.db.queries');

        self::assertCount(2, $gauges);
        self::assertEquals($tags, $gauges[0]['tags']);
        self::assertEquals($tags, $gauges[1]['tags']);
        self::assertEquals(1, $gauges[0]['value']);
        self::assertEquals(2, $gauges[1]['value']);
    }

    public function provideEvents(): array
    {
        $jobMock =  $this->createMock(RedisJob::class);
        $jobMock->method('resolveName')->willReturn('redisJob');

        return [
            [
                new JobProcessing('test', $jobMock),
                new JobProcessed('test', $jobMock),
                [
                    'status' => 'processed',
                    'queue' => $jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($jobMock->resolveName())
                ]
            ],
            [
                new JobProcessing('test', $jobMock),
                new JobFailed('test', $jobMock, new Exception('test exception')),
                [
                    'status' => 'failed',
                    'queue' => $jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new Exception('test exception')))
                ]
            ],
            [
                new JobProcessing('test', $jobMock),
                new JobExceptionOccurred('test', $jobMock, new Exception('test exception')),
                [
                    'status' => 'exceptionOccurred',
                    'queue' => $jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new Exception('test exception')))
                ]
            ]
        ];
    }
}
