<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Listeners;

use AirSlate\Datadog\Services\Datadog;

/**
 * Class EventBusListener
 *
 * @package AirSlate\Datadog\Listenres
 */
class EventBusListener
{
    /**
     * @var Datadog
     */
    private $datadog;

    /**
     * EventBusListener constructor.
     *
     * @param Datadog $datadog
     */
    public function __construct(Datadog $datadog)
    {
        $this->datadog = $datadog;
    }

    /**
     * @param mixed $event
     * @throws \Exception
     */
    public function handle($event) : void
    {
        if ($event instanceof \AirSlate\Event\Events\ProcessedEvent) {
            $this->datadog->increment('app.eventbus.receive', 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'processed',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\RejectedEvent) {
            $this->datadog->increment('app.eventbus.receive', 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'rejected',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\RejectedEvent) {
            $this->datadog->increment('app.eventbus.receive', 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'retried',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\SendEvent) {
            $this->datadog->increment('app.ventbus.send', 1, [
                'key' => $event->getRoutingKey(),
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\SendToQueueEvent) {
            $this->datadog->increment('app.eventbus.sendtoqueue', 1, [
                'queue' => $event->getQueueName(),
            ]);
        }
    }
}
