<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarCalendarioItem;

class EliminarCalendarioItemHandler
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
     * @param EliminarCalendarioItem $command
     * @return void
     */
    public function __invoke(EliminarCalendarioItem $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->calendarioItemRepository->byId($command->id);
            $this->calendarioItemRepository->delete($command->id);
        });
    }
}








