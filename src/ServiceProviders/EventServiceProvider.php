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
                  ->give($this->app->get('config')->get('datadog.namespace', 'namespace'));

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
            'Illuminate\Database\Events\CacheHit',
            'Illuminate\Database\Events\CacheMissed',
            'Illuminate\Database\Events\KeyForgotten',
            'Illuminate\Database\Events\KeyWritten',
            'Illuminate\Database\Database\QueryExecuted',
            'Illuminate\Database\Database\TransactionBeginning',
            'Illuminate\Database\Database\TransactionCommitted',
            'Illuminate\Database\Database\TransactionRolledBack',
        ], EventBusListener::class);
    }
}
