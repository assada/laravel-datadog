<?php
declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Listeners\EventBusListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

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
            'AirSlate\Event\Events\Processed',
            'AirSlate\Event\Events\Rejected',
            'AirSlate\Event\Events\Retried',
            'AirSlate\Event\Events\Send',
            'AirSlate\Event\Events\SendToQueueEvent',
        ], EventBusListener::class);
    }
}
