<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Http\Middleware;

use AirSlate\Datadog\Services\DatabaseQueryCounter;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class DatadogMiddleware
 *
 * @package AirSlate\Datadog\Http\Middleware
 */
class DatadogMiddleware
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var Datadog
     */
    private $datadog;

    /**
     * @var DatabaseQueryCounter
     */
    private $queryCounter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DatadogMiddleware constructor.
     *
     * @param string $namespace
     * @param Datadog $datadog
     * @param DatabaseQueryCounter $queryCounter
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $namespace,
        Datadog $datadog,
        DatabaseQueryCounter $queryCounter,
        LoggerInterface $logger
    ) {
        $this->namespace = $namespace;
        $this->datadog = $datadog;
        $this->queryCounter = $queryCounter;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param $next
     *
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        $start = \defined('LARAVEL_START') ? floatval(LARAVEL_START) : microtime(true);
        $response = $next($request);

        try {
            $this->sendMetrics($request, $response, $start);
        } catch (Throwable $exception) {
            $this->logger->error('Cannot send metrics.', [
                'excpetion' => $exception,
            ]);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param float $start
     */
    private function sendMetrics(Request $request, Response $response, float $start): void
    {
        $duration = microtime(true) - $start;

        $tags = [
            'code' => $response->getStatusCode(),
            'method' => $request->method(),
        ];

        // send response time
        $this->datadog->timing("{$this->namespace}.response_time", $duration, 1, $tags);

        // send memory_get_peak_usage
        $this->datadog->gauge("{$this->namespace}.memory_peak_usage", memory_get_peak_usage(false), 1, $tags);
        $this->datadog->gauge("{$this->namespace}.memory_peak_usage_real", memory_get_peak_usage(true), 1, $tags);

        // send query count
        $this->datadog->gauge("{$this->namespace}.db.queries", $this->queryCounter->getCount(), 1, $tags);
    }
}
