<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Infrastructure\Persistence\Model\EntregaEvidencia;

/**
 * @class EntregaEvidenciaRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class EntregaEvidenciaRepository implements EntregaEvidenciaRepositoryInterface
{
    /**
     * @param string $eventId
     * @param array $data
     * @return void
     */
    public function upsertByEventId(string $eventId, array $data): void
    {
        EntregaEvidencia::query()->updateOrCreate(
            ['event_id' => $eventId],
            $data
        );
    }
}
