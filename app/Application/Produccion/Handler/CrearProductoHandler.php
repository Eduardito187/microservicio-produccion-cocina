<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearProducto;
use App\Domain\Produccion\Entity\Products;

class CrearProductoHandler
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
     * @param CrearProducto $command
     * @return int
     */
    public function __invoke(CrearProducto $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $product = new Products(
                null,
                $command->sku,
                $command->price,
                $command->specialPrice
            );

            $this->productRepository->save($product);
            $saved = $this->productRepository->bySku($command->sku);

            return $saved->id;
        });
    }
}








