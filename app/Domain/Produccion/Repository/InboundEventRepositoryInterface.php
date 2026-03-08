<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\InboundEvent;

/**
 * @class InboundEventRepositoryInterface
 */
interface InboundEventRepositoryInterface
{
    /**
     * @return int
     */
    public function save(InboundEvent $event): string;
}
