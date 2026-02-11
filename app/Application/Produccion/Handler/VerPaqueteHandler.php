<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\PaqueteRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerPaquete;
use App\Domain\Produccion\Entity\Paquete;

/**
 * @class VerPaqueteHandler
 * @package App\Application\Produccion\Handler
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
     * @param VerPaquete $command
     * @return array
     */
    public function __invoke(VerPaquete $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $paquete = $this->paqueteRepository->byId($command->id);
            return $this->mapPaquete($paquete);
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
