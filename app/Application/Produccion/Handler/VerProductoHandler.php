<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerProducto;
use App\Domain\Produccion\Entity\Products;

/**
 * @class VerProductoHandler
 * @package App\Application\Produccion\Handler
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
     * @param VerProducto $command
     * @return array
     */
    public function __invoke(VerProducto $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $product = $this->productRepository->byId((string) $command->id);
            return $this->mapProducto($product);
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
