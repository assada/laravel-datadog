<?php

declare(strict_types=1);

namespace AirSlate\Tests\Unit\Components;

use AirSlate\Datadog\Components\DbTransactionsComponent;
use AirSlate\Tests\Unit\BaseTestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class TransactionComponentTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->make(DbTransactionsComponent::class)->register();
    }

    /**
     * @dataProvider provideEvents
     *
     * @param $startEvent
     * @param $endEvent
     * @param $startTags
     * @param $endTags
     */
    public function testTransaction($startEvent, $endEvent, $startTags, $endTags): void
    {
        event($startEvent);

        event($endEvent);

        $increments = $this->datastub->getIncrements('airslate.db.transaction');

        self::assertCount(2, $increments);
        self::assertEquals($startTags, $increments[0]['tags']);
        self::assertEquals($endTags, $increments[1]['tags']);
    }

    public function provideEvents(): array
    {
        $connection = $this->createMock(Connection::class);

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
