<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration;

use App\Application\Produccion\Handler\DespachadorOPHandler;
use App\Application\Produccion\Handler\GenerarOPHandler;
use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Produccion\Command\GenerarOP;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use DateTimeImmutable;

/**
 * @class RecalculoProduccionService
 * @package App\Application\Integration
 */
class RecalculoProduccionService
{
    /**
     * @var GenerarOPHandler
     */
    private $generarOPHandler;

    /**
     * @var DespachadorOPHandler
     */
    private $despachadorOPHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param GenerarOPHandler $generarOPHandler
     * @param DespachadorOPHandler $despachadorOPHandler
     * @param LoggerInterface $logger
     */
    public function __construct(
        GenerarOPHandler $generarOPHandler,
        DespachadorOPHandler $despachadorOPHandler,
        LoggerInterface $logger = new NullLogger()
    ) {
        $this->generarOPHandler = $generarOPHandler;
        $this->despachadorOPHandler = $despachadorOPHandler;
        $this->logger = $logger;
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function tryGenerarOP(array $payload): bool
    {
        $fecha = $payload['fecha'] ?? null;
        $sucursalId = $payload['sucursalId'] ?? ($payload['sucursal_id'] ?? null);
        $items = $payload['items'] ?? null;

        if (!is_string($fecha) || $fecha === '' || $sucursalId === null || !is_array($items)) {
            $this->logger->info('Recalculo OP skipped (missing fecha/sucursalId/items)');
            return false;
        }

        $command = new GenerarOP(
            $payload['ordenProduccionId'] ?? null,
            new DateTimeImmutable($fecha),
            $sucursalId,
            $items
        );

        $this->generarOPHandler->__invoke($command);
        return true;
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function tryDespacharOP(array $payload): bool
    {
        $ordenProduccionId = $payload['ordenProduccionId'] ?? ($payload['orden_produccion_id'] ?? null);
        $itemsDespacho = $payload['itemsDespacho'] ?? ($payload['items_despacho'] ?? null);

        if (!is_string($ordenProduccionId) || $ordenProduccionId === '' || !is_array($itemsDespacho)) {
            $this->logger->info('Recalculo despacho skipped (missing ordenProduccionId/itemsDespacho)');
            return false;
        }

        $command = new DespachadorOP([
            'ordenProduccionId' => $ordenProduccionId,
            'itemsDespacho' => $itemsDespacho,
            'pacienteId' => $payload['pacienteId'] ?? ($payload['paciente_id'] ?? null),
            'direccionId' => $payload['direccionId'] ?? ($payload['direccion_id'] ?? null),
            'ventanaEntrega' => $payload['ventanaEntregaId'] ?? ($payload['ventana_entrega_id'] ?? null),
        ]);

        $this->despachadorOPHandler->__invoke($command);
        return true;
    }
}
