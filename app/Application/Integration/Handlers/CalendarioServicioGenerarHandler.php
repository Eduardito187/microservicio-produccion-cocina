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
 * @package App\Application\Integration\Handlers
 */
class CalendarioServicioGenerarHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ?LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Evento de solicitud de generación de calendario.
     *
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $this->logger->info('Evento de generacion de servicio de calendario recibido', [
            'payload' => $payload,
            'meta' => $meta,
        ]);
    }
}
