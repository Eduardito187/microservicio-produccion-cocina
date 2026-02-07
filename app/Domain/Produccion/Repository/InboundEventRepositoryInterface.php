<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\InboundEvent;

interface InboundEventRepositoryInterface
{
    /**
     * @param InboundEvent $event
     * @return int
     */
    public function save(InboundEvent $event): string;
}
