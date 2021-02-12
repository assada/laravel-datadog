<?php

return [
    'application_namespace' => env('APP_NAMESPACE', 'airslate'),
    'application_name' => env('APP_NAME', 'unknown'),
    'statsd_server' => env('STATSD_HOST', '172.17.0.1'),
    'statsd_port' => env('STATSD_PORT', 8125),
    'statsd_env' => env('APP_ENV'),
    'is_send_increment_metric_with_timing_metric' => false,

    'components' => [
        'http' => [
            \AirSlate\Datadog\Components\ResponseTimeComponent::class,
            \AirSlate\Datadog\Components\HttpQueryCounterComponent::class,
            \AirSlate\Datadog\Components\HttpMemoryUsageComponent::class,
        ],
        'console' => [
            \AirSlate\Datadog\Components\JobTimingComponent::class,
            \AirSlate\Datadog\Components\JobQueryCounterComponent::class,
        ],
        'all' => [
            \AirSlate\Datadog\Components\CacheHitsComponent::class,
            \AirSlate\Datadog\Components\DbTransactionsComponent::class,
            \AirSlate\Datadog\Components\DbQueryExecutedComponent::class,
            \AirSlate\Datadog\Components\CustomEventsComponent::class,
        ]
    ],
    'global_tags' => [],
];
