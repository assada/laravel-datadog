<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\JobTimingComponent;
use AirSlate\Datadog\Models\Timer;
use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Tests\Unit\BaseTestCase;
use Exception;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\RedisJob;

class JobTimingComponentTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(JobTimingComponent::class)->register();
    }

    /**
     * @dataProvider provideEvents
     *
     * @param $jobProcessing
     * @param $jobEnded
     * @param $tags
     */
    public function testJobTimingSuccess($jobProcessing, $jobEnded, $tags): void
    {
        $timer = new Timer();

        $timer->start();

        event($jobProcessing);
        usleep(10000);
        event($jobEnded);

        $timer->stop();

        $timing = $this->datastub->getTimings('airslate.queue.job')[0];

        self::assertTrue($timing['value'] > 0.01 && $timer->getInterval() > $timing['value']);
        self::assertEquals($tags, $timing['tags']);
        self::assertEquals(1, $timing['sample_rate']);
    }

    public function provideEvents(): array
    {
        $jobMock = $this->createMock(RedisJob::class);
        $jobMock->method('resolveName')->willReturn('redisJob');

        return [
            [
                new JobProcessing('test', $jobMock),
                new JobProcessed('test', $jobMock),
                [
                    'status' => 'processed',
                    'queue' => $jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($jobMock->resolveName())
                ],
            ],
            [
                new JobProcessing('test', $jobMock),
                new JobFailed('test', $jobMock, new Exception('test exception')),
                [
                    'status' => 'failed',
                    'queue' => $jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new Exception('test exception')))
                ],
            ],
            [
                new JobProcessing('test', $jobMock),
                new JobExceptionOccurred('test', $jobMock, new Exception('test exception')),
                [
                    'status' => 'exceptionOccurred',
                    'queue' => $jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new Exception('test exception')))
                ],
            ]
        ];
    }
}
