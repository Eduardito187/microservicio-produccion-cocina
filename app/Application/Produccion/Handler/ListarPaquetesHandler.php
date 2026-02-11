<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarPaquetes;
use App\Domain\Produccion\Entity\Paquete;

/**
 * @class ListarPaquetesHandler
 * @package App\Application\Produccion\Handler
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
     *
     * @param PaqueteRepositoryInterface $paqueteRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        PaqueteRepositoryInterface $paqueteRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->paqueteRepository = $paqueteRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ListarPaquetes $command
     * @return array
     */
    public function __invoke(ListarPaquetes $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapPaquete'], $this->paqueteRepository->list());
        });
    }

    /**
     * @param Paquete $paquete
     * @return array
     */
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
