<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearProducto;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\Events\ProductoCreado;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;

/**
 * @class CrearProductoHandler
 */
class CrearProductoHandler
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
     * @var DomainEventPublisherInterface
     */
    private $eventPublisher;

    /**
     * Constructor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->productRepository = $productRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @return int
     */
    public function __invoke(CrearProducto $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $product = new Products(
                null,
                $command->sku,
                $command->price,
                $command->specialPrice,
                $command->nombre
            );

            $id = $this->productRepository->save($product);
            $event = new ProductoCreado($id, $command->sku, $command->price, $command->specialPrice, $command->nombre);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
