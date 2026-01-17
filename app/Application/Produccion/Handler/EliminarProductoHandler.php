<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarProducto;

class EliminarProductoHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    public readonly ProductRepositoryInterface $productRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->productRepository = $productRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarProducto $command
     * @return void
     */
    public function __invoke(EliminarProducto $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->productRepository->byId((string) $command->id);
            $this->productRepository->delete($command->id);
        });
    }
}








