<?php

return [
    'application_namespace' => env('APP_NAMESPACE', 'airslate'),
    'application_name' => env('APP_NAME', 'unknown'),
    'statsd_server' => env('STATSD_HOST', '172.17.0.1'),
    'statsd_port' => env('STATSD_PORT', 8125),
    'statsd_env' => env('APP_ENV'),
    'is_send_increment_metric_with_timing_metric' => false,

    /*
    |--------------------------------------------------------------------------
    | Datadog Events Configuration
    |--------------------------------------------------------------------------
    | Sends message to datadog metric when some event calls. Just remove FQN to disable event listening.
    */
    'events' => [
        'defaultEvents' => [
            AirSlate\EventBusHelper\Events\ProcessedEvent::class,
            AirSlate\EventBusHelper\Events\RejectedEvent::class,
            AirSlate\EventBusHelper\Events\RetryEvent::class,
            AirSlate\EventBusHelper\Events\SendEvent::class,
            AirSlate\EventBusHelper\Events\SendToQueueEvent::class,
            Illuminate\Queue\Events\JobProcessing::class,
            Illuminate\Queue\Events\JobProcessed::class,
            Illuminate\Queue\Events\JobExceptionOccurred::class,
            Illuminate\Queue\Events\JobFailed::class,
            Illuminate\Cache\Events\CacheHit::class,
            Illuminate\Cache\Events\CacheMissed::class,
            Illuminate\Cache\Events\KeyForgotten::class,
            Illuminate\Cache\Events\KeyWritten::class,
            Illuminate\Database\Events\QueryExecuted::class,
            Illuminate\Database\Events\TransactionBeginning::class,
            Illuminate\Database\Events\TransactionCommitted::class,
            Illuminate\Database\Events\TransactionRolledBack::class,
        ],
        'customEvents' => [
            //Your custom events FQN
        ]
    ]
    'global_tags' => [],
];
