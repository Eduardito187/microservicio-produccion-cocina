<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarProductos;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;

/**
 * @class ListarProductosHandler
 */
class ListarProductosHandler
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

    public function __invoke(ListarProductos $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapProducto'], $this->productRepository->list());
        });
    }

    private function mapProducto(Products $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'price' => $product->price,
            'special_price' => $product->special_price,
        ];
    }
}
