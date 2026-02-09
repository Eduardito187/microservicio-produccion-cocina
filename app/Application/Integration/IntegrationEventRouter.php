<?php

namespace App\Application\Integration;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class IntegrationEventRouter
{
    /**
     * @var array<string, IntegrationEventHandlerInterface>
     */
    private array $handlers;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param array<string, IntegrationEventHandlerInterface> $handlers
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $handlers = [], ?LoggerInterface $logger = null)
    {
        $this->handlers = $handlers;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string $eventName
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function dispatch(string $eventName, array $payload, array $meta = []): void
    {
        $handler = $this->handlers[$eventName] ?? null;
        if (!$handler) {
            $this->logger->warning('Integration event ignored (no handler)', [
                'event' => $eventName,
                'meta' => $meta,
            ]);
            return;
        }

        $handler->handle($payload, $meta);
    }

    /**
     * @param string $eventName
     * @param IntegrationEventHandlerInterface $handler
     * @return void
     */
    public function register(string $eventName, IntegrationEventHandlerInterface $handler): void
    {
        $this->handlers[$eventName] = $handler;
    }
}
