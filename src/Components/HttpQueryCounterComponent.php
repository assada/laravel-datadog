<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Datadog\Services\CounterManager;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Events\RequestHandled;

class HttpQueryCounterComponent extends ComponentAbstract
{
    /** @var CounterManager */
    private $counterManager;

    public function __construct(
        Application $application,
        ClassShortener $classShortener,
        Datadog $datadog,
        Dispatcher $dispatcher,
        CounterManager $counterManager
    ) {
        parent::__construct($application, $classShortener, $datadog, $dispatcher);
        $this->counterManager = $counterManager;
    }

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $this->counterManager->startCounter($this->getStat('db.queries'));

        $this->listen(QueryExecuted::class, function (): void {
            $this->counterManager->incrementCounter($this->getStat('db.queries'));
        });

        $this->listen(RequestHandled::class, function (RequestHandled $requestHandled): void {
            $tags = [
                'code' => $requestHandled->response->getStatusCode(),
                'method' => $requestHandled->request->method(),
            ];

            $this->statsd->gauge(
                $this->getStat('db.queries'),
                $this->counterManager->getValue($this->getStat('db.queries')),
                1,
                $tags
            );
        });
    }
}
