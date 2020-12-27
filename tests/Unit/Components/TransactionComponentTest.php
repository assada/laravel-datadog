<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class TransactionComponentTest extends BaseTestCase
{
    /**
     * @dataProvider provideEvents
     */
    public function testTransaction($startEvent, $endEvent, $startTags, $endTags)
    {
        event($startEvent);
        event($endEvent);
        $increments = $this->datastub->getIncrements('airslate.db.transaction');
        $this->assertEquals(2, count($increments));
        $this->assertEquals($startTags, $increments[0]['tags']);
        $this->assertEquals($endTags, $increments[1]['tags']);
    }

    public function provideEvents()
    {
        $connection = $this->createConnectionMock();
        return [
            [
                new TransactionBeginning($connection),
                new TransactionCommitted($connection),
                [
                    'status' => 'begin',
                ],
                [
                    'status' => 'commit',
                ]
            ],
            [
                new TransactionBeginning($connection),
                new TransactionRolledBack($connection),
                [
                    'status' => 'begin',
                ],
                [
                    'status' => 'rollback',
                ]
            ]
        ];
    }
}
