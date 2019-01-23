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
            'AirSlate\Event\Events\ProcessedEvent',
            'AirSlate\Event\Events\RejectedEvent',
            'AirSlate\Event\Events\RetriedEvent',
            'AirSlate\Event\Events\SendEvent',
            'AirSlate\Event\Events\SendToQueueEvent',
            'Illuminate\Queue\Events\JobProcessing',
            'Illuminate\Queue\Events\JobProcessed',
            'Illuminate\Queue\Events\JobExceptionOccurred',
            'Illuminate\Queue\Events\JobFailed',
        ], EventBusListener::class);
    }
}
