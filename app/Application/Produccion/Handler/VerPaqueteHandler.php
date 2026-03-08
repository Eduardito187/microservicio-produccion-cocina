<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerPaquete;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paquete;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;

/**
 * @class VerPaqueteHandler
 */
class VerPaqueteHandler
{
    /**
     * @var PaqueteRepositoryInterface
     */
    private $paqueteRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->paqueteRepository = $paqueteRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(VerPaquete $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $paquete = $this->paqueteRepository->byId($command->id);

            return $this->mapPaquete($paquete);
        });
    }

    private function mapPaquete(Paquete $paquete): array
    {
        return [
            'id' => $paquete->id,
            'etiqueta_id' => $paquete->etiquetaId,
            'ventana_id' => $paquete->ventanaId,
            'direccion_id' => $paquete->direccionId,
        ];
    }
}
