<?php
declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Listeners\EventBusListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Events\Dispatcher;

/**
 * Class EventServiceProvider
 *
 * @package AirSlate\Datadog\ServiceProviders
 */
class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->get(Dispatcher::class);

        $dispatcher->listen([
            'event-bus.processed',
            'event-bus.rejected',
            'event-bus.retried',
            'event-bus.send',
            'event-bus.send-to-queue',
        ], EventBusListener::class);
    }
}
