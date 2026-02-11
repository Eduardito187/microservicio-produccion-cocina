<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerVentanaEntrega;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\VentanaEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class VerVentanaEntregaHandler
 * @package App\Application\Produccion\Handler
 */
class VerVentanaEntregaHandler
{
    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private VentanaEntregaRepositoryInterface $ventanaEntregaRepository;

    /**
     * @var TransactionAggregate
     */
    private TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param VentanaEntregaRepositoryInterface $ventanaEntregaRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param VerVentanaEntrega $command
     * @return array
     */
    public function __invoke(VerVentanaEntrega $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $ventana = $this->ventanaEntregaRepository->byId($command->id);
            return $this->mapVentana($ventana);
        });
    }

    /**
     * @param VentanaEntrega $ventana
     * @return array
     */
    private function mapVentana(VentanaEntrega $ventana): array
    {
        return [
            'id' => $ventana->id,
            'desde' => $ventana->desde->format('Y-m-d H:i:s'),
            'hasta' => $ventana->hasta->format('Y-m-d H:i:s'),
        ];
    }
}
