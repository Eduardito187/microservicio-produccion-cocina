<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class ContratoConsultarHandler
 */
class ContratoConsultarHandler implements IntegrationEventHandlerInterface
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
     * Evento de consulta sin efecto de escritura en este microservicio.
     */
    public function handle(array $payload, array $meta = []): void
    {
        $this->logger->info('Evento de consulta de contrato recibido (sin accion)', [
            'payload' => $payload,
            'meta' => $meta,
        ]);
    }
}
