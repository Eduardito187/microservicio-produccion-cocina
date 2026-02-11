<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarCalendarioItem;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Events\CalendarioItemActualizado;

/**
 * @class ActualizarCalendarioItemHandler
 * @package App\Application\Produccion\Handler
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
     *
     * @param CalendarioItemRepositoryInterface $calendarioItemRepository
     * @param TransactionAggregate $transactionAggregate
     * @param DomainEventPublisherInterface $eventPublisher
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
     * @param ActualizarCalendarioItem $command
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
