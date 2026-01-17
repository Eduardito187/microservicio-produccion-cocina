<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\InboundEvent;

interface InboundEventRepositoryInterface
{
    /**
     * @param string $eventId
     * @return bool
     */
    public function existsByEventId(string $eventId): bool;

    /**
     * @param InboundEvent $event
     * @return int
     */
    public function save(InboundEvent $event): int;
}
