<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarProductos;
use App\Domain\Produccion\Entity\Products;

/**
 * @class ListarProductosHandler
 * @package App\Application\Produccion\Handler
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
     * @param ListarProductos $command
     * @return array
     */
    public function __invoke(ListarProductos $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapProducto'], $this->productRepository->list());
        });
    }

    /**
     * @param Products $product
     * @return array
     */
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
