<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerProducto;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;

/**
 * @class VerProductoHandler
 */
class VerProductoHandler
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

    public function __invoke(VerProducto $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $product = $this->productRepository->byId((string) $command->id);

            return $this->mapProducto($product);
        });
    }

    private function mapProducto(Products $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'nombre' => $product->nombre,
            'price' => $product->price,
            'special_price' => $product->special_price,
        ];
    }
}
