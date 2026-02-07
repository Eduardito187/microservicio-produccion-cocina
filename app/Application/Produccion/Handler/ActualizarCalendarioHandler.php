<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarCalendario;

class ActualizarCalendarioHandler
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
     * @param ActualizarCalendario $command
     * @return int
     */
    public function __invoke(ActualizarCalendario $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $calendario = $this->calendarioRepository->byId($command->id);
            $calendario->fecha = $command->fecha;
            $calendario->sucursalId = $command->sucursalId;

            return $this->calendarioRepository->save($calendario);
        });
    }
}








