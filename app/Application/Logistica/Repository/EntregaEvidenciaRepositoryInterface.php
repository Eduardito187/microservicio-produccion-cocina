<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Logistica\Repository;

/**
 * @class EntregaEvidenciaRepositoryInterface
 * @package App\Application\Logistica\Repository
 */
interface EntregaEvidenciaRepositoryInterface
{
    /**
     * @param string $eventId
     * @param array $data
     * @return void
     */
    public function upsertByEventId(string $eventId, array $data): void;
}
