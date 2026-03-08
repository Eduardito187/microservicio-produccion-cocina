<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Support\Transaction;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;

/**
 * @class TransactionAggregate
 */
class TransactionAggregate
{
    private TransactionManagerInterface $transactionManager;

    /**
     * Constructor
     */
    public function __construct(TransactionManagerInterface $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    public function runTransaction(callable $callback): mixed
    {
        return $this->transactionManager->run($callback);
    }

    public function afterCommit(callable $callback): void
    {
        $this->transactionManager->afterCommit($callback);
    }
}
