<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class IntegrationEventRouter
 */
class IntegrationEventRouter
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(array $handlers = [], ?LoggerInterface $logger = null)
    {
        $this->handlers = $handlers;
        $this->logger = $logger ?? new NullLogger;
    }

    public function dispatch(string $eventName, array $payload, array $meta = []): void
    {
        $handler = $this->handlers[$eventName] ?? null;
        if (! $handler) {
            $this->logger->warning('Evento de integracion ignorado (sin handler)', [
                'event' => $eventName,
                'meta' => $meta,
            ]);

            return;
        }

        $handler->handle($payload, $meta);
    }

    public function register(string $eventName, IntegrationEventHandlerInterface $handler): void
    {
        $this->handlers[$eventName] = $handler;
    }
}
