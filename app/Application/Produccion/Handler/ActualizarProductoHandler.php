<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarProducto;
use App\Domain\Produccion\Entity\Products;

class ActualizarProductoHandler
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
     * @param ActualizarProducto $command
     * @return int
     */
    public function __invoke(ActualizarProducto $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $this->productRepository->byId((string) $command->id);

            $product = new Products(
                id: $command->id,
                sku: $command->sku,
                price: $command->price,
                special_price: $command->specialPrice
            );

            $this->productRepository->save($product);

            return $command->id;
        });
    }
}
