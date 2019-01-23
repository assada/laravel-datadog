<?php

return [
    'environment' => env('APP_ENV', 'dev'),
    'application_name' => env('APP_NAME', 'unknown'),
    'application_version' => env('APPLICATION_VERSION', 'unknown'),
    'statsd_server' => env('STATSD_SERVER', '172.17.0.1'), // docker's localhost
    'statsd_port' => env('STATSD_PORT', 8125),
];
