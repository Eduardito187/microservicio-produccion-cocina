<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Produccion\Command\EliminarCalendarioItem;
use App\Application\Support\Transaction\TransactionAggregate;

/**
 * @class EliminarCalendarioItemHandler
 * @package App\Application\Produccion\Handler
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
