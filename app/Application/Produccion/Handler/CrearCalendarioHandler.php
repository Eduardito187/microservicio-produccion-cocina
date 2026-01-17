<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearCalendario;
use App\Domain\Produccion\Entity\Calendario;

class CrearCalendarioHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    public readonly CalendarioRepositoryInterface $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

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
     * @param CrearCalendario $command
     * @return int
     */
    public function __invoke(CrearCalendario $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $calendario = new Calendario(null, $command->fecha, $command->sucursalId);

            return $this->calendarioRepository->save($calendario);
        });
    }
}








