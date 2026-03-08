<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Transaction;

use App\Application\Shared\OutboxUnitOfWorkInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

/**
 * @class TransactionManager
 */
class TransactionManager implements TransactionManagerInterface
{
    /**
     * @var OutboxUnitOfWorkInterface
     */
    private $outboxUnitOfWork;

    /**
     * Constructor
     */
    public function __construct(OutboxUnitOfWorkInterface $outboxUnitOfWork)
    {
        $this->outboxUnitOfWork = $outboxUnitOfWork;
    }

    public function run(callable $callback): mixed
    {
        $this->outboxUnitOfWork->clear();

        try {
            return DB::transaction(function () use ($callback): mixed {
                $result = $callback();
                // Persist pending domain events in outbox before commit.
                $this->outboxUnitOfWork->flush();

                return $result;
            });
        } finally {
            $this->outboxUnitOfWork->clear();
        }
    }

    public function afterCommit(callable $callback): void
    {
        DB::afterCommit($callback);
    }
}
