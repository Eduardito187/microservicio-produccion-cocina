<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\CrearCalendarioItem;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Events\CalendarioItemCreado;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;

/**
 * @class CrearCalendarioItemHandler
 */
class CrearCalendarioItemHandler
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
    public function __invoke(CrearCalendarioItem $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $item = new CalendarioItem(null, $command->calendarioId, $command->itemDespachoId);

            $id = $this->calendarioItemRepository->save($item);
            $event = new CalendarioItemCreado($id, $command->calendarioId, $command->itemDespachoId);
            $this->eventPublisher->publish([$event], $id);

            return $id;
        });
    }
}
