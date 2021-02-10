<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use Illuminate\Database\Events\QueryExecuted;

class DbQueryExecutedComponent extends ComponentAbstract
{
    public function register(): void
    {
        $this->listen(QueryExecuted::class, function (): void {
            $this->statsd->increment($this->getStat('db.query'), 1, [
                'status' => 'executed',
            ]);
        });
    }
}
