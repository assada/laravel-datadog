<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\CacheHitsComponent;
use AirSlate\Datadog\Components\JobTimingComponent;
use AirSlate\Datadog\Models\Timer;
use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Tests\Stub\TestException;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\RedisJob;

class JobTimingComponentTest extends BaseTestCase
{
    /** @var RedisJob */
    private $jobMock;

    /**
     * @dataProvider provideEvents
     */
    public function testJobTimingSuccess($jobProcessing, $jobEnded, $tags, $sampleRate)
    {
        $timer = new Timer('test');

        $timer->start();

        event($jobProcessing);
        sleep(1);
        event($jobEnded);

        $timer->stop();

        $timing = $this->datastub->getTimings('airslate.queue.job')[0];

        $this->assertTrue($timing['value'] > 1 && $timer->getInteval() > $timing['value']);
        $this->assertEquals($tags, $timing['tags']);
        $this->assertEquals($sampleRate, $timing['sample_rate']);
    }

    public function provideEvents()
    {
        $this->jobMock = $this->createJobMock();

        return [
            [
                new JobProcessing('test', $this->jobMock),
                new JobProcessed('test', $this->jobMock),
                [
                    'status' => 'processed',
                    'queue' => $this->jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($this->jobMock->resolveName())
                ],
                1
            ],
            [
                new JobProcessing('test', $this->jobMock),
                new JobFailed('test', $this->jobMock, new TestException('test exception')),
                [
                    'status' => 'failed',
                    'queue' => $this->jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($this->jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new TestException('test exception')))
                ],
                1
            ],
            [
                new JobProcessing('test', $this->jobMock),
                new JobExceptionOccurred('test', $this->jobMock, new TestException('test exception')),
                [
                    'status' => 'exceptionOccurred',
                    'queue' => $this->jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($this->jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new TestException('test exception')))
                ],
                1
            ]
        ];
    }
}
