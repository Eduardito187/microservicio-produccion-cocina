<?php

namespace App\Application\Logistica\Repository;

interface EntregaEvidenciaRepositoryInterface
{
    /**
     * @param string $eventId
     * @param array $data
     * @return void
     */
    public function upsertByEventId(string $eventId, array $data): void;
}
