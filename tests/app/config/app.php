<?php

declare(strict_types=1);

return [
    'providers' => [
        \Illuminate\Cache\CacheServiceProvider::class,
        \AirSlate\Datadog\ServiceProviders\ComponentsProvider::class
    ]
];