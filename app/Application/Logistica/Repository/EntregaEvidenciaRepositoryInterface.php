<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Logistica\Repository;

/**
 * @class EntregaEvidenciaRepositoryInterface
 */
interface EntregaEvidenciaRepositoryInterface
{
    public function upsertByEventId(string $eventId, array $data): void;
}
