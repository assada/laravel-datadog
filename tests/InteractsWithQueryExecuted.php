<?php

declare(strict_types=1);

namespace AirSlate\Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Orchestra\Testbench\TestCase;

/**
 * @mixin TestCase
 */
trait InteractsWithQueryExecuted
{
    public function riseQueryExecutedEvent(): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getName')->willReturn('test_connection');

        event(new QueryExecuted('sql', ['test' => 1], 10, $connectionMock));
    }
}
