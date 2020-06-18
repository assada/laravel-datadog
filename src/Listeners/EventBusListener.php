<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Listeners;

use AirSlate\Datadog\Services\Datadog;
use AirSlate\Datadog\Services\QueueJobMeter;
use AirSlate\EventBusHelper\Events\ProcessedEvent;
use AirSlate\EventBusHelper\Events\SendToQueueEvent;
use AirSlate\EventBusHelper\Events\SendEvent;
use AirSlate\EventBusHelper\Events\RetryEvent;
use AirSlate\EventBusHelper\Events\RejectedEvent;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Laravel\Horizon\Events\JobFailed;

/**
 * Class EventBusListener
 *
 * @package AirSlate\Datadog\Listenres
 * @property Datadog $datadog
 * @property QueueJobMeter $meter
 * @property string $namespace
 * @property array $allowEvents
 * @property array $customEvents
 */
class EventBusListener
{
    /**
     * @var Datadog
     */
    protected $datadog;

    /**
     * @var QueueJobMeter
     */
    protected $meter;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $allowEvents;

    /**
     * @var array
     */
    protected $customEvents;

    /**
     * EventBusListener constructor.
     *
     * @param Datadog $datadog
     * @param QueueJobMeter $meter
     * @param string $namespace
     * @param array $events
     */
    public function __construct(Datadog $datadog, QueueJobMeter $meter, string $namespace, array $events)
    {
        $this->datadog = $datadog;
        $this->meter = $meter;
        $this->namespace = $namespace;
        $this->allowEvents = $events['defaultEvents'];
        $this->customEvents = $events['customEvents'];
    }

    /**
     * @param mixed $event
     * @throws \Exception
     */
    public function handle($event): void
    {
        if ($event instanceof ProcessedEvent && in_array(ProcessedEvent::class, $this->allowEvents)) {
            $this->datadog->timing("{$this->namespace}.eventbus.receive", $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'processed',
            ]);
        } elseif ($event instanceof RejectedEvent && in_array(RejectedEvent::class, $this->allowEvents)) {
            $this->datadog->timing("{$this->namespace}.eventbus.receive", $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'rejected',
            ]);
        } elseif ($event instanceof RetryEvent && in_array(RetryEvent::class, $this->allowEvents)) {
            $this->datadog->timing("{$this->namespace}.eventbus.receive", $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'retried',
            ]);
        } elseif ($event instanceof SendEvent && in_array(SendEvent::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.eventbus.send", 1, [
                'key' => $event->getRoutingKey(),
            ]);
        } elseif ($event instanceof SendToQueueEvent && in_array(SendToQueueEvent::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.eventbus.sendtoqueue", 1, [
                'queue' => $event->getQueueName(),
            ]);
        } elseif ($event instanceof JobProcessing && in_array(JobProcessing::class, $this->allowEvents)) {
            $this->meter->start($event->job);
        } elseif ($event instanceof JobProcessed && in_array(JobProcessed::class, $this->allowEvents)) {
            $this->datadog->timing("{$this->namespace}.queue.job", $this->meter->stop($event->job), 1, [
                'status' => 'processed',
                'queue' => $event->job->getQueue(),
                'task' => $this->getClassShortName($event->job->resolveName())
            ]);
        } elseif ($event instanceof JobExceptionOccurred && in_array(JobExceptionOccurred::class, $this->allowEvents)) {
            $this->datadog->timing("{$this->namespace}.queue.job", $this->meter->stop($event->job), 1, [
                'status' => 'exceptionOccurred',
                'queue' => $event->job->getQueue(),
                'task' => $this->getClassShortName($event->job->resolveName()),
                'exception' => $this->getClassShortName(get_class($event->exception))
            ]);
        } elseif ($event instanceof JobFailed && in_array(JobFailed::class, $this->allowEvents)) {
            $this->datadog->timing("{$this->namespace}.queue.job", $this->meter->stop($event->job), 1, [
                'status' => 'failed',
                'queue' => $event->job->getQueue(),
                'task' => $this->getClassShortName($event->job->resolveName()),
                'exception' => $this->getClassShortName(get_class($event->exception))
            ]);
        } elseif ($event instanceof CacheHit && in_array(CacheHit::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'hit',
            ]);
        } elseif ($event instanceof CacheMissed && in_array(CacheMissed::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'miss',
            ]);
        } elseif ($event instanceof KeyForgotten && in_array(KeyForgotten::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'del',
            ]);
        } elseif ($event instanceof KeyWritten && in_array(KeyWritten::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'put',
            ]);
        } elseif ($event instanceof QueryExecuted && in_array(QueryExecuted::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.db.query", 1, [
                'status' => 'executed',
            ]);
        } elseif ($event instanceof TransactionBeginning && in_array(TransactionBeginning::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.db.transaction", 1, [
                'status' => 'begin',
            ]);
        } elseif ($event instanceof TransactionCommitted && in_array(TransactionCommitted::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.db.transaction", 1, [
                'status' => 'commit',
            ]);
        } elseif ($event instanceof TransactionRolledBack && in_array(TransactionRolledBack::class, $this->allowEvents)) {
            $this->datadog->increment("{$this->namespace}.db.transaction", 1, [
                'status' => 'rollback',
            ]);
        }
    }

    /**
     * @param string $alias
     * @return string
     * @throws \ReflectionException
     */
    private function getClassShortName(string $alias): string
    {
        if (class_exists($alias)) {
            return (new \ReflectionClass($alias))->getShortName();
        }
        return $alias;
    }

    /**
     * @param mixed $event
     * @return float
     */
    private function getDuration($event): float
    {
        return method_exists($event, 'getDuration') ? (float) $event->getDuration() : 0.0;
    }
}
