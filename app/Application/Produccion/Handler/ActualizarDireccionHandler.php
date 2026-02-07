<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ActualizarDireccion;

class ActualizarDireccionHandler
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
     * @param ActualizarDireccion $command
     * @return int
     */
    public function __invoke(ActualizarDireccion $command): string
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): string {
            $direccion = $this->direccionRepository->byId($command->id);
            $direccion->nombre = $command->nombre;
            $direccion->linea1 = $command->linea1;
            $direccion->linea2 = $command->linea2;
            $direccion->ciudad = $command->ciudad;
            $direccion->provincia = $command->provincia;
            $direccion->pais = $command->pais;
            $direccion->geo = $command->geo;

            return $this->direccionRepository->save($direccion);
        });
    }
}








