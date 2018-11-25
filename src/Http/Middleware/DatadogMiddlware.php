<?php

namespace AirSlate\Datadog\Http\Middleware;

use Illuminate\Container\Container;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatadogMiddlware
{

    /**
     * @var Datadog
     */
    private $dataDog;

    public function __construct()
    {
        $this->dataDog = Container::getInstance()->make(Datadog::class);
    }

    public function handle(Request $request, $next)
    {
        $startTimer = microtime(true);
        $response = $next($request);
        $this->sendMetrics($request, $response, $startTimer);
        return $response;
    }

    private function sendMetrics(Request $request, JsonResponse $response, float $startTimer = null): void
    {
        $start = \defined('LARAVEL_START') ? LARAVEL_START : $startTimer;
        $duration = microtime(true) - $start;

        $tags = [
            'status_code' => $response->getStatusCode(),
            'method' => $request->method(),
        ];

        $this->dataDog->timing('request_time', $duration, 1, $tags);
    }
}
