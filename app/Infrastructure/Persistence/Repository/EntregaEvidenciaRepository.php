<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Infrastructure\Persistence\Model\EntregaEvidencia;

/**
 * @class EntregaEvidenciaRepository
 */
class EntregaEvidenciaRepository implements EntregaEvidenciaRepositoryInterface
{
    public function upsertByEventId(string $eventId, array $data): void
    {
        EntregaEvidencia::query()->updateOrCreate(
            ['event_id' => $eventId],
            $data
        );
    }
}
