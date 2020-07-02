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
        $this->app->when(EventBusListener::class)
            ->needs('$namespace')
            ->give($this->app->get('config')->get('datadog.application_namespace', 'unknown'));

        $this->app->when(EventBusListener::class)
            ->needs('$events')
            ->give($this->app->get('config')->get('datadog.events'));

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->get(Dispatcher::class);

        $eventClasses = array_merge(
            $this->app->get('config')->get('datadog.events.defaultEvents'),
            $this->app->get('config')->get('datadog.events.customEvents')
        );
        $dispatcher->listen($eventClasses, EventBusListener::class);
    }
}
