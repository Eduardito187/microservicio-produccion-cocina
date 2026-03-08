<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarPaquetes;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Paquete;
use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;

/**
 * @class ListarPaquetesHandler
 */
class ListarPaquetesHandler
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

    public function __invoke(ListarPaquetes $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapPaquete'], $this->paqueteRepository->list());
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
