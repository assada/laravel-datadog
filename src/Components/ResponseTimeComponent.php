<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use AirSlate\Datadog\Exceptions\ComponentRegistrationException;
use Illuminate\Foundation\Http\Events\RequestHandled;

class ResponseTimeComponent extends ComponentAbstract
{
    public function register(): void
    {
        if (!\defined('LARAVEL_START')) {
            throw new ComponentRegistrationException("LARAVEL_START constant isn't defined. 
            Please define it in index.php");
        }
        $laravelStart = (float) LARAVEL_START;

        $this->listen(RequestHandled::class, function (RequestHandled $requestHandled) use ($laravelStart): void {
            $tags = [
                'code' => $requestHandled->response->getStatusCode(),
                'method' => $requestHandled->request->method(),
            ];

            $this->statsd->timing(
                $this->getStat('response_time'),
                microtime(true) - $laravelStart,
                1,
                $tags
            );
        });
    }
}
