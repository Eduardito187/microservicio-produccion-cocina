<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarCalendarioItem;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Events\CalendarioItemActualizado;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;

/**
 * @class ActualizarCalendarioItemHandler
 */
class ActualizarCalendarioItemHandler
{
    /**
     * @var CalendarioItemRepositoryInterface
     */
    private $calendarioItemRepository;

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
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @return int
     */
    public function __invoke(ActualizarCalendarioItem $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $item = $this->calendarioItemRepository->byId($command->id);
            $item->calendarioId = $command->calendarioId;
            $item->itemDespachoId = $command->itemDespachoId;

            $id = $this->calendarioItemRepository->save($item);
            $event = new CalendarioItemActualizado($id, $item->calendarioId, $item->itemDespachoId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
