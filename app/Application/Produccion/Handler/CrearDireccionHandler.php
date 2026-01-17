<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\CrearDireccion;
use App\Domain\Produccion\Entity\Direccion;

class CrearDireccionHandler
{
    /**
     * @var DireccionRepositoryInterface
     */
    public readonly DireccionRepositoryInterface $direccionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param CrearDireccion $command
     * @return int
     */
    public function __invoke(CrearDireccion $command): int
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): int {
            $direccion = new Direccion(
                null,
                $command->nombre,
                $command->linea1,
                $command->linea2,
                $command->ciudad,
                $command->provincia,
                $command->pais,
                $command->geo
            );

            return $this->direccionRepository->save($direccion);
        });
    }
}








