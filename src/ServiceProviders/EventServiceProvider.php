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

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->get(Dispatcher::class);

        $dispatcher->listen([
            'AirSlate\EventBusHelper\Events\ProcessedEvent',
            'AirSlate\EventBusHelper\Events\RejectedEvent',
            'AirSlate\EventBusHelper\Events\RetriedEvent',
            'AirSlate\EventBusHelper\Events\SendEvent',
            'AirSlate\EventBusHelper\Events\SendToQueueEvent',
            'Illuminate\Queue\Events\JobProcessing',
            'Illuminate\Queue\Events\JobProcessed',
            'Illuminate\Queue\Events\JobExceptionOccurred',
            'Illuminate\Queue\Events\JobFailed',
            'Illuminate\Cache\Events\CacheHit',
            'Illuminate\Cache\Events\CacheMissed',
            'Illuminate\Cache\Events\KeyForgotten',
            'Illuminate\Cache\Events\KeyWritten',
            'Illuminate\Database\Events\QueryExecuted',
            'Illuminate\Database\Events\TransactionBeginning',
            'Illuminate\Database\Events\TransactionCommitted',
            'Illuminate\Database\Events\TransactionRolledBack',
        ], EventBusListener::class);
    }
}
