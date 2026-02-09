<?php

namespace App\Application\Integration;

interface IntegrationEventHandlerInterface
{
    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void;
}
