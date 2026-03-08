<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration;

/**
 * @class IntegrationEventHandlerInterface
 */
interface IntegrationEventHandlerInterface
{
    public function handle(array $payload, array $meta = []): void;
}
