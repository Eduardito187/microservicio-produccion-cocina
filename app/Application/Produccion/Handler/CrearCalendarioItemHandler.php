<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearCalendarioItem;
use App\Domain\Produccion\Entity\CalendarioItem;

class CrearCalendarioItemHandler
{
    /**
     * @var CalendarioItemRepositoryInterface
     */
    public readonly CalendarioItemRepositoryInterface $calendarioItemRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param CalendarioItemRepositoryInterface $calendarioItemRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param CrearCalendarioItem $command
     * @return int
     */
    public function __invoke(CrearCalendarioItem $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $item = new CalendarioItem(null, $command->calendarioId, $command->itemDespachoId);

            return $this->calendarioItemRepository->save($item);
        });
    }
}








