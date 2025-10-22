<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\GenerarOP;
use App\Domain\Produccion\ValueObjects\OrderItem;
use App\Domain\Produccion\Model\OrderItems;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\ValueObjects\Qty;

class GenerarOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param GenerarOP $command
     * @return string|int|null
     */
    public function __invoke(GenerarOP $command): string|int|null
    {
        $items = [];

        foreach ($command->items as $item) {
            $items[] = new OrderItem(new Sku($item['sku']), new Qty($item['qty']));
        }

        $orderItems = OrderItems::fromArray($items);

        return $this->transactionAggregate->runTransaction(function () use ($command, $orderItems): int {
            $ordenProduccion = AggregateOrdenProduccion::crear( $command->fecha,  $command->sucursalId,  $orderItems);
            $ordenProduccion->agregarItems($orderItems);
            return $this->ordenProduccionRepository->save($ordenProduccion, true, true);
        });
    }
}