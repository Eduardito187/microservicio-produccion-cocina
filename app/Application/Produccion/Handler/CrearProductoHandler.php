<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearProducto;
use App\Domain\Produccion\Entity\Products;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\ProductoCreado;

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
     * @var DomainEventPublisherInterface
     */
    private readonly DomainEventPublisherInterface $eventPublisher;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
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

            $id = $this->productRepository->save($product);
            $event = new ProductoCreado($id, $command->sku, $command->price, $command->specialPrice);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}








