<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\JobQueryCounterComponent;
use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Tests\Stub\TestException;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class JobQueryCounterComponentTest extends BaseTestCase
{
    /**
     * @dataProvider provideEvents
     */
    public function testSuccess($jobStarted, $jobEnded, $tags)
    {
        event($jobStarted);
        $this->riseEventQueryExecuted();
        event($jobEnded);
        $gauges = $this->datastub->getGauges('airslate.queue.db.queries');

        $this->assertEquals(1, count($gauges));
        $this->assertEquals($tags, $gauges[0]['tags']);
        $this->assertEquals(1, $gauges[0]['value']);
    }

    /**
     *
     * @dataProvider provideEvents
     */
    public function testTwoCalls($jobStarted, $jobEnded, $tags)
    {
        event($jobStarted);
        $this->riseEventQueryExecuted();
        event($jobEnded);
        event($jobStarted);
        $this->riseEventQueryExecuted();
        $this->riseEventQueryExecuted();
        event($jobEnded);

        $gauges = $this->datastub->getGauges('airslate.queue.db.queries');

        $this->assertEquals(2, count($gauges));
        $this->assertEquals($tags, $gauges[0]['tags']);
        $this->assertEquals($tags, $gauges[1]['tags']);
        $this->assertEquals(1, $gauges[0]['value']);
        $this->assertEquals(2, $gauges[1]['value']);
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
                ]
            ],
            [
                new JobProcessing('test', $this->jobMock),
                new JobFailed('test', $this->jobMock, new TestException('test exception')),
                [
                    'status' => 'failed',
                    'queue' => $this->jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($this->jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new TestException('test exception')))
                ]
            ],
            [
                new JobProcessing('test', $this->jobMock),
                new JobExceptionOccurred('test', $this->jobMock, new TestException('test exception')),
                [
                    'status' => 'exceptionOccurred',
                    'queue' => $this->jobMock->getQueue(),
                    'task' => (new ClassShortener())->shorten($this->jobMock->resolveName()),
                    'exception' => (new ClassShortener())->shorten(get_class(new TestException('test exception')))
                ]
            ]
        ];
    }

}
