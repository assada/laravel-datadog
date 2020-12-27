<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class DbTransactionsComponent extends ComponentAbstract
{
    public function register(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->get(Dispatcher::class);

        $dispatcher->listen(TransactionBeginning::class, function (TransactionBeginning $transactionBeginning) {
            $this->statsd->increment($this->getStat('db.transaction'), 1, [
                'status' => 'begin',
            ]);
        });

        $dispatcher->listen(TransactionCommitted::class, function (TransactionCommitted $transactionCommitted) {
            $this->statsd->increment($this->getStat('db.transaction'), 1, [
                'status' => 'commit',
            ]);
        });

        $dispatcher->listen(TransactionRolledBack::class, function (TransactionRolledBack $transactionRolledBack) {
            $this->statsd->increment($this->getStat("db.transaction"), 1, [
                'status' => 'rollback',
            ]);
        });
    }
}
