<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Produccion\Command\ActualizarEstadoPaqueteDesdeLogisticaCommand;
use App\Application\Produccion\Handler\ActualizarEstadoPaqueteDesdeLogisticaHandler;
use App\Application\Produccion\Service\DeliveryEvidenceImageStore;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class LogisticaPaqueteEstadoActualizadoHandler
 */
class LogisticaPaqueteEstadoActualizadoHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var ActualizarEstadoPaqueteDesdeLogisticaHandler
     */
    private $commandHandler;

    /**
     * @var DeliveryEvidenceImageStore
     */
    private $imageStore;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ActualizarEstadoPaqueteDesdeLogisticaHandler $commandHandler,
        DeliveryEvidenceImageStore $imageStore,
        ?LoggerInterface $logger = null
    ) {
        $this->commandHandler = $commandHandler;
        $this->imageStore = $imageStore;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $eventId = $meta['event_id'] ?? null;
        if (! is_string($eventId) || $eventId === '') {
            $this->logger->warning('logistica.paquete.estado-actualizado ignorado (falta event_id)');

            return;
        }

        $packageId = $this->getString($payload, ['packageId', 'paqueteId', 'package_id', 'paquete_id']);
        if ($packageId === null || $packageId === '') {
            $this->logger->warning('logistica.paquete.estado-actualizado ignorado (falta package id)', [
                'event_id' => $eventId,
            ]);

            return;
        }

        $deliveryStatus = $this->getString($payload, ['deliveryStatus', 'delivery_status', 'status']) ?? '';
        $occurredOn = $this->getString($payload, ['occurredOn', 'occurred_on', 'updatedAt', 'updated_at', 'timestamp']);
        $driverId = $this->getString($payload, ['driverId', 'driver_id', 'repartidorId', 'driver']);

        $rawEvidence = $payload['deliveryEvidence'] ?? null;
        $resolvedEvidence = $this->resolveEvidence($packageId, $eventId, $rawEvidence);

        $command = new ActualizarEstadoPaqueteDesdeLogisticaCommand(
            $eventId,
            $packageId,
            strtolower(trim($deliveryStatus)),
            $occurredOn,
            $driverId,
            $resolvedEvidence,
            $payload
        );

        ($this->commandHandler)($command);
    }

    private function resolveEvidence(string $packageId, string $eventId, mixed $evidence): mixed
    {
        if (! is_string($evidence) || trim($evidence) === '') {
            return $evidence;
        }

        if (! $this->imageStore->isBase64Image($evidence)) {
            return $evidence;
        }

        $url = $this->imageStore->store($packageId, $eventId, $evidence);

        return $url !== '' ? $url : null;
    }

    private function getString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $payload)) {
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
