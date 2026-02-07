<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarCalendarioItem;

class ActualizarCalendarioItemHandler
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
     * @param ActualizarCalendarioItem $command
     * @return int
     */
    public function __invoke(ActualizarCalendarioItem $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $item = $this->calendarioItemRepository->byId($command->id);
            $item->calendarioId = $command->calendarioId;
            $item->itemDespachoId = $command->itemDespachoId;

            return $this->calendarioItemRepository->save($item);
        });
    }
}








