<?php

namespace App\Application\Support\Transaction\Interface;

interface TransactionManagerInterface
{
    /**
     * @param callable $callback
     * @return mixed
     */
    public function run(callable $callback): mixed;

    /**
     * @param callable $callback
     * @return void
     */
    public function afterCommit(callable $callback): void;
}