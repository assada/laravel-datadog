<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Datadog\Services\Datadog;
use AirSlate\Datadog\Services\TimerManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class JobTimingComponent extends ComponentAbstract
{
    /** @var TimerManager */
    private $timerManager;

    public function __construct(
        Application $application,
        ClassShortener $classShortener,
        Datadog $datadog,
        Dispatcher $dispatcher,
        TimerManager $timerManager
    ) {
        parent::__construct(
            $application,
            $classShortener,
            $datadog,
            $dispatcher
        );
        $this->timerManager = $timerManager;
    }

    public function register(): void
    {
        $this->listen(JobProcessing::class, function (JobProcessing $jobProcessing) {
            $this->timerManager->startTimer($this->getStat('queue.job'));
        });

        $this->listen(JobProcessed::class, function (JobProcessed $jobProcessed) {
            $tags = [
                'status' => 'processed',
                'queue' => $jobProcessed->job->getQueue(),
                'task' => $this->classShortener->shorten($jobProcessed->job->resolveName())
            ];

            $timer = $this->timerManager->stopTimer($this->getStat('queue.job'));

            $this->statsd->timing(
                $this->getStat('queue.job'),
                $timer->getInteval(),
                1,
                $tags
            );
        });

        $this->listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event) {
            $tags = [
                'status' => 'exceptionOccurred',
                'queue' => $event->job->getQueue(),
                'task' => $this->classShortener->shorten($event->job->resolveName()),
                'exception' => $this->classShortener->shorten(get_class($event->exception))
            ];

            $timer = $this->timerManager->stopTimer($this->getStat('queue.job'));

            $this->statsd->timing(
                $this->getStat('queue.job'),
                $timer->getInteval(),
                1,
                $tags
            );
        });

        $this->listen(JobFailed::class, function (JobFailed $event) {
            $tags = [
                'status' => 'failed',
                'queue' => $event->job->getQueue(),
                'task' => $this->getShortName($event->job->resolveName()),
                'exception' => $this->getShortName(get_class($event->exception))
            ];

            $timer = $this->timerManager->stopTimer($this->getStat('queue.job'));

            $this->statsd->timing(
                $this->getStat('queue.job'),
                $timer->getInteval(),
                1,
                $tags
            );
        });
    }
}
