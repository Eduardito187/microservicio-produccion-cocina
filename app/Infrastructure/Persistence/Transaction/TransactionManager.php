<?php
namespace App\Infrastructure\Persistence\Transaction;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

class TransactionManager implements TransactionManagerInterface
{
    /**
     * @param callable $callback
     */
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function afterCommit(callable $callback): void
    {
       // DB::afterCommit($callback);
    }
}