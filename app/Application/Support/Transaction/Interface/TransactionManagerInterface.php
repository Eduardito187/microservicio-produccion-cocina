<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Support\Transaction\Interface;

/**
 * @class TransactionManagerInterface
 */
interface TransactionManagerInterface
{
    public function run(callable $callback): mixed;

    public function afterCommit(callable $callback): void;
}
