<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Tests\Unit\BaseTestCase;

class QueryExecutedComponentTest extends BaseTestCase
{
    /**
     * @test
     */
    public function queryExecuted()
    {
        $this->riseEventQueryExecuted();
        $increments = $this->datastub->getIncrements('airslate.db.query');
        $this->assertEquals($increments[0]['tags'], [
            'status' => 'executed',
        ]);
    }
}
