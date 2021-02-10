<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\DbQueryExecutedComponent;
use AirSlate\Tests\InteractsWithQueryExecuted;
use AirSlate\Tests\Unit\BaseTestCase;

class QueryExecutedComponentTest extends BaseTestCase
{
    use InteractsWithQueryExecuted;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(DbQueryExecutedComponent::class)->register();
    }

    public function testQueryExecuted(): void
    {
        $this->riseQueryExecutedEvent();

        $increments = $this->datastub->getIncrements('airslate.db.query');

        self::assertEquals($increments[0]['tags'], [
            'status' => 'executed',
        ]);
    }
}
