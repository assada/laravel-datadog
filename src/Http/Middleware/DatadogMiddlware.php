<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Http\Middleware;

use Illuminate\Container\Container;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DatadogMiddlware
 *
 * @package AirSlate\Datadog\Http\Middleware
 */
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
            'status_code' => $response->getStatusCode(),
            'method' => $request->method(),
        ];

        $this->dataDog->timing('request_time', $duration, 1, $tags);
    }
}
