<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Http\Middleware;

use Illuminate\Container\Container;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DatadogMiddleware
 *
 * @package AirSlate\Datadog\Http\Middleware
 */
class DatadogMiddleware
{
    /**
     * @var Datadog
     */
    private $datadog;

    public function __construct()
    {
        $this->datadog = Container::getInstance()->make(Datadog::class);
    }

    /**
     * @param Request $request
     * @param $next
     *
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        $startTimer = microtime(true);
        $response = $next($request);
        $this->sendMetrics($request, $response, $startTimer);
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param float|null $startTimer
     */
    private function sendMetrics(Request $request, Response $response, float $startTimer = null): void
    {
        $start = \defined('LARAVEL_START') ? LARAVEL_START : $startTimer;
        $duration = microtime(true) - $start;

        $tags = [
            'code' => $response->getStatusCode(),
            'method' => $request->method(),
        ];

        /*
        // send response size
        $this->datadog->gauge('airslate.request_size', strlen($request->getContent()), 1, $tags);

        // send response size
        $this->datadog->gauge('airslate.response_size', strlen($response->getContent()), 1, $tags);
        */

        // send response time
        $this->datadog->timing('airslate.response_time', $duration, 1, $tags);
    }
}
