<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration;

use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Produccion\Command\GenerarOP;
use App\Application\Produccion\Handler\DespachadorOPHandler;
use App\Application\Produccion\Handler\GenerarOPHandler;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class RecalculoProduccionService
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
     */
    public function __construct(
        GenerarOPHandler $generarOPHandler,
        DespachadorOPHandler $despachadorOPHandler,
        LoggerInterface $logger = new NullLogger
    ) {
        $this->generarOPHandler = $generarOPHandler;
        $this->despachadorOPHandler = $despachadorOPHandler;
        $this->logger = $logger;
    }

    public function tryGenerarOP(array $payload): bool
    {
        $fecha = $payload['fecha'] ?? null;
        $items = $payload['items'] ?? null;

        if (! is_string($fecha) || $fecha === '' || ! is_array($items)) {
            $this->logger->info('Recalculo OP omitido (falta fecha/items)');

            return false;
        }

        $command = new GenerarOP(
            $payload['ordenProduccionId'] ?? null,
            new DateTimeImmutable($fecha),
            $items
        );

        $this->generarOPHandler->__invoke($command);

        return true;
    }

    public function tryDespacharOP(array $payload): bool
    {
        $ordenProduccionId = $payload['ordenProduccionId'] ?? ($payload['orden_produccion_id'] ?? null);
        $itemsDespacho = $payload['itemsDespacho'] ?? ($payload['items_despacho'] ?? null);

        if (! is_string($ordenProduccionId) || $ordenProduccionId === '' || ! is_array($itemsDespacho)) {
            $this->logger->info('Recalculo de despacho omitido (falta ordenProduccionId/itemsDespacho)');

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
