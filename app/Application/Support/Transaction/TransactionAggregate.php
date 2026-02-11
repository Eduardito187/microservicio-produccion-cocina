<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Support\Transaction;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;

/**
 * @class TransactionAggregate
 * @package App\Application\Support\Transaction
 */
class TransactionAggregate
{
    /**
     * @var TransactionManagerInterface
     */
    private TransactionManagerInterface $transactionManager;

    /**
     * Constructor
     *
     * @param TransactionManagerInterface $transactionManager
     */
    public function __construct(TransactionManagerInterface $transactionManager) {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param callable $callback
     */
    public function runTransaction(callable $callback): mixed
    {
        return $this->transactionManager->run($callback);
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function afterCommit(callable $callback): void
    {
        $this->transactionManager->afterCommit($callback);
    }
}
