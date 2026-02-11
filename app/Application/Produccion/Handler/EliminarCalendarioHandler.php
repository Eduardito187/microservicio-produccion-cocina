<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\EliminarCalendario;

/**
 * @class EliminarCalendarioHandler
 * @package App\Application\Produccion\Handler
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
     *
     * @param CalendarioRepositoryInterface $calendarioRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param EliminarCalendario $command
     * @return void
     */
    public function __invoke(EliminarCalendario $command): void
    {
        $this->transactionAggregate->runTransaction(function () use ($command): void {
            $this->calendarioRepository->byId($command->id);
            $this->calendarioRepository->delete($command->id);
        });
    }
}
