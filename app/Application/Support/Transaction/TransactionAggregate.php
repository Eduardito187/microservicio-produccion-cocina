<?php

namespace App\Application\Support\Transaction;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;

class TransactionAggregate
{
    /**
     * @var TransactionManagerInterface
     */
    private readonly TransactionManagerInterface $transactionManager;

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
}