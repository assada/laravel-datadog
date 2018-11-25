<?php

return [
    'statsd_server' => env('STATSD_SERVER', '172.17.0.1'), // docker's localhost
    'statsd_port' => env('STATSD_POST', 8125),
];
