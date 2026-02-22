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
 * @package App\Application\Integration\Handlers
 */
class ContratoConsultarHandler implements IntegrationEventHandlerInterface
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
     * Evento de consulta sin efecto de escritura en este microservicio.
     *
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $this->logger->info('Contrato consultar event received (no-op)', [
            'payload' => $payload,
            'meta' => $meta,
        ]);
    }
}

