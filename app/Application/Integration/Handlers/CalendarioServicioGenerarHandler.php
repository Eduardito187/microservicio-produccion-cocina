<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class CalendarioServicioGenerarHandler
 */
class CalendarioServicioGenerarHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger;
    }

    /**
     * Evento de solicitud de generación de calendario.
     */
    public function handle(array $payload, array $meta = []): void
    {
        $this->logger->info('Evento de generacion de servicio de calendario recibido', [
            'payload' => $payload,
            'meta' => $meta,
        ]);
    }
}
