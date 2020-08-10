<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Listeners;

use AirSlate\Datadog\Events\DatadogEventExtendedInterface;
use AirSlate\Datadog\Services\DatabaseQueryCounter;
use AirSlate\Datadog\Events\DatadogEventInterface;
use AirSlate\Datadog\Events\DatadogEventJobInterface;
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
use Illuminate\Queue\Events\JobFailed;
use DomainException;

/**
 * Class EventBusListener
 *
 * @package AirSlate\Datadog\Listenres
 * @property Datadog $datadog
 * @property QueueJobMeter $meter
 * @property string $namespace
 * @property array $defaultEvents
 * @property array $customEvents
 * @property DatabaseQueryCounter $queryCounter
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
     * @var DatabaseQueryCounter
     */
    protected $queryCounter;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $defaultEvents;

    /**
     * @var array
     */
    protected $customEvents;

    /**
     * EventBusListener constructor.
     *
     * @param Datadog $datadog
     * @param QueueJobMeter $meter
     * @param DatabaseQueryCounter $queryCounter
     * @param string $namespace
     * @param array $events
     */
    public function __construct(
        string $namespace,
        Datadog $datadog,
        QueueJobMeter $meter,
        DatabaseQueryCounter $queryCounter,
        array $events
    ) {
        $this->namespace = $namespace;
        $this->datadog = $datadog;
        $this->meter = $meter;
        $this->namespace = $namespace;
        $this->defaultEvents = $events['defaultEvents'];
        $this->customEvents = $events['customEvents'];
        $this->queryCounter = $queryCounter;
    }

    /**
     * @param mixed $event
     * @throws \Exception
     */
    public function handle($event): void
    {
        if ($event instanceof ProcessedEvent && in_array(ProcessedEvent::class, $this->defaultEvents)) {
            $this->datadog->timing("{$this->namespace}.eventbus.receive", $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'processed',
            ]);
        } elseif ($event instanceof RejectedEvent && in_array(RejectedEvent::class, $this->defaultEvents)) {
            $this->datadog->timing("{$this->namespace}.eventbus.receive", $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'rejected',
            ]);
        } elseif ($event instanceof RetryEvent && in_array(RetryEvent::class, $this->defaultEvents)) {
            $this->datadog->timing("{$this->namespace}.eventbus.receive", $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'retried',
            ]);
        } elseif ($event instanceof SendEvent && in_array(SendEvent::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.eventbus.send", 1, [
                'key' => $event->getRoutingKey(),
            ]);
        } elseif ($event instanceof SendToQueueEvent && in_array(SendToQueueEvent::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.eventbus.sendtoqueue", 1, [
                'queue' => $event->getQueueName(),
            ]);
        } elseif ($event instanceof JobProcessing && in_array(JobProcessing::class, $this->defaultEvents)) {
            $this->meter->start($event->job);
            $this->queryCounter->flush();
        } elseif ($event instanceof JobProcessed && in_array(JobProcessed::class, $this->defaultEvents)) {
            $tags = [
                'status' => 'processed',
                'queue' => $event->job->getQueue(),
                'task' => $this->getClassShortName($event->job->resolveName())
            ];
            $this->datadog->timing("{$this->namespace}.queue.job", $this->meter->stop($event->job), 1, $tags);
            $this->datadog->gauge("{$this->namespace}.queue.db.queries", $this->queryCounter->getCount(), 1, $tags);
        } elseif ($event instanceof JobExceptionOccurred
            && in_array(JobExceptionOccurred::class, $this->defaultEvents)) {
            $tags = [
                'status' => 'exceptionOccurred',
                'queue' => $event->job->getQueue(),
                'task' => $this->getClassShortName($event->job->resolveName()),
                'exception' => $this->getClassShortName(get_class($event->exception))
            ];
            $this->datadog->timing("{$this->namespace}.queue.job", $this->meter->stop($event->job), 1, $tags);
            $this->datadog->gauge("{$this->namespace}.queue.db.queries", $this->queryCounter->getCount(), 1, $tags);
        } elseif ($event instanceof JobFailed && in_array(JobFailed::class, $this->defaultEvents)) {
            $tags = [
                'status' => 'failed',
                'queue' => $event->job->getQueue(),
                'task' => $this->getClassShortName($event->job->resolveName()),
                'exception' => $this->getClassShortName(get_class($event->exception))
            ];
            $this->datadog->timing("{$this->namespace}.queue.job", $this->meter->stop($event->job), 1, $tags);
            $this->datadog->gauge("{$this->namespace}.queue.db.queries", $this->queryCounter->getCount(), 1, $tags);
        } elseif ($event instanceof CacheHit && in_array(CacheHit::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'hit',
            ]);
        } elseif ($event instanceof CacheMissed && in_array(CacheMissed::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'miss',
            ]);
        } elseif ($event instanceof KeyForgotten && in_array(KeyForgotten::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'del',
            ]);
        } elseif ($event instanceof KeyWritten && in_array(KeyWritten::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.cache.item", 1, [
                'status' => 'put',
            ]);
        } elseif ($event instanceof QueryExecuted && in_array(QueryExecuted::class, $this->defaultEvents)) {
            $this->queryCounter->increment();
            $this->datadog->increment("{$this->namespace}.db.query", 1, [
                'status' => 'executed',
            ]);
        } elseif ($event instanceof TransactionBeginning
            && in_array(TransactionBeginning::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.db.transaction", 1, [
                'status' => 'begin',
            ]);
        } elseif ($event instanceof TransactionCommitted
            && in_array(TransactionCommitted::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.db.transaction", 1, [
                'status' => 'commit',
            ]);
        } elseif ($event instanceof TransactionRolledBack
            && in_array(TransactionRolledBack::class, $this->defaultEvents)) {
            $this->datadog->increment("{$this->namespace}.db.transaction", 1, [
                'status' => 'rollback',
            ]);
        }

        // Custom events
        if ($event instanceof DatadogEventInterface) {
            $this->sendCustomMetric($event);
        }
    }

    private function sendCustomMetric(DatadogEventInterface $event)
    {
        $stats = "{$this->namespace}.{$event->getEventCategory()}.{$event->getEventName()}";

        if ($event instanceof DatadogEventExtendedInterface) {
            switch ($event->getMetricType()) {
                case DatadogEventExtendedInterface::METRIC_TYPE_INCREMENT:
                    $this->datadog->increment($stats, 1, $event->getTags(), $event->getValue());
                    break;
                case DatadogEventExtendedInterface::METRIC_TYPE_DECREMENT:
                    $this->datadog->decrement($stats, 1, $event->getTags(), $event->getValue());
                    break;
                case DatadogEventExtendedInterface::METRIC_TYPE_HISTOGRAM:
                    $this->datadog->histogram($stats, $event->getValue(), 1, $event->getTags());
                    break;
                case DatadogEventExtendedInterface::METRIC_TYPE_GAUGE:
                    $this->datadog->gauge($stats, $event->getValue(), 1, $event->getTags());
                    break;
                case DatadogEventExtendedInterface::METRIC_TYPE_TIMING:
                    $this->datadog->timing($stats, $event->getValue(), 1, $event->getTags());
                    break;
                default:
                    throw new DomainException('metric' . $event->getMetricType() . ' is not found in ' .
                        DatadogEventExtendedInterface::class . ' allowed metrics');
            }
        } else {
            $this->datadog->increment($stats, 1, $event->getTags(), 1);
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
