<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\EliminarCalendario;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;

/**
 * @class EliminarCalendarioHandler
 */
class EliminarCalendarioHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(EliminarCalendario $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->calendarioRepository->byId($command->id);
            $this->calendarioRepository->delete($command->id);
        });
    }
}
