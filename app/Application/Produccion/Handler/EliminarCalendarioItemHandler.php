<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarCalendarioItem;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;

/**
 * @class EliminarCalendarioItemHandler
 */
class EliminarCalendarioItemHandler
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
     * Constructor
     */
    public function __construct(
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarCalendarioItem $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->calendarioItemRepository->byId($command->id);
            $this->calendarioItemRepository->delete($command->id);
        });
    }
}
