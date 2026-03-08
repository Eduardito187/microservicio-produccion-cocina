<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarProducto;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;

/**
 * @class EliminarProductoHandler
 */
class EliminarProductoHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->productRepository = $productRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarProducto $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->productRepository->byId((string) $command->id);
            $this->productRepository->delete($command->id);
        });
    }
}
