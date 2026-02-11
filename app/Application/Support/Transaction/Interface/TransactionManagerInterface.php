<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Support\Transaction\Interface;

/**
 * @class TransactionManagerInterface
 * @package App\Application\Support\Transaction\Interface
 */
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
