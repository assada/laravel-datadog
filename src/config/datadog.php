<?php

return [
    'application' => env('APP_NAME', 'unknown'),
    'statsd_server' => env('STATSD_SERVER', '172.17.0.1'), // docker's localhost
    'statsd_port' => env('STATSD_PORT', 8125),
];
