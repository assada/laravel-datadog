<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use Illuminate\Foundation\Http\Events\RequestHandled;

class MemoryPeakUsageComponent extends ComponentAbstract
{
    public function register(): void
    {
        $this->listen(RequestHandled::class, function (RequestHandled $requestHandled): void {
            $tags = [
                'code' => $requestHandled->response->getStatusCode(),
                'method' => $requestHandled->request->method(),
            ];

            $this->statsd->gauge(
                $this->getStat('memory_peak_usage'),
                memory_get_peak_usage(false),
                1,
                $tags
            );

            $this->statsd->gauge(
                $this->getStat('memory_peak_usage_real'),
                memory_get_peak_usage(true),
                1,
                $tags
            );
        });
    }
}
