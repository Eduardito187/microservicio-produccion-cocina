<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarProducto;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\Events\ProductoActualizado;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;

/**
 * @class ActualizarProductoHandler
 */
class ActualizarProductoHandler
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
    public function __invoke(ActualizarProducto $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $this->productRepository->byId((string) $command->id);

            $product = new Products(
                id: $command->id,
                sku: $command->sku,
                price: $command->price,
                special_price: $command->specialPrice,
                nombre: $command->nombre
            );

            $id = $this->productRepository->save($product);
            $event = new ProductoActualizado($id, $command->sku, $command->price, $command->specialPrice, $command->nombre);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
