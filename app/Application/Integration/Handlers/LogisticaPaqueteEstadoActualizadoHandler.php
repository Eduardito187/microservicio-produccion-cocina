<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Produccion\Handler\ActualizarEstadoPaqueteDesdeLogisticaHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class LogisticaPaqueteEstadoActualizadoHandler
 * @package App\Application\Integration\Handlers
 */
class LogisticaPaqueteEstadoActualizadoHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var ActualizarEstadoPaqueteDesdeLogisticaHandler
     */
    private $commandHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ActualizarEstadoPaqueteDesdeLogisticaHandler $commandHandler
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        ActualizarEstadoPaqueteDesdeLogisticaHandler $commandHandler,
        ?LoggerInterface $logger = null
    ) {
        $this->commandHandler = $commandHandler;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $eventId = $meta['event_id'] ?? null;
        if (!is_string($eventId) || $eventId === '') {
            $this->logger->warning('logistica.paquete.estado-actualizado ignored (missing event_id)');
            return;
        }

        $packageId = $this->getString($payload, ['packageId', 'paqueteId', 'package_id', 'paquete_id']);
        if ($packageId === null || $packageId === '') {
            $this->logger->warning('logistica.paquete.estado-actualizado ignored (missing package id)', [
                'event_id' => $eventId,
            ]);
            return;
        }

        $deliveryStatus = $this->getString($payload, ['deliveryStatus', 'delivery_status', 'status']) ?? '';
        $occurredOn = $this->getString($payload, ['occurredOn', 'occurred_on', 'updatedAt', 'updated_at', 'timestamp']);
        $driverId = $this->getString($payload, ['driverId', 'driver_id', 'repartidorId', 'driver']);

        $command = new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            $eventId,
            $packageId,
            $deliveryStatus,
            $occurredOn,
            $driverId,
            $payload['deliveryEvidence'] ?? null,
            $payload
        );

        ($this->commandHandler)($command);
    }

    /**
     * @param array $payload
     * @param array $keys
     * @return ?string
     */
    private function getString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            if ($value === null || $value === '') {
                return null;
            }
            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }
            if (is_string($value)) {
                return $value;
            }
        }

        return null;
    }
}
