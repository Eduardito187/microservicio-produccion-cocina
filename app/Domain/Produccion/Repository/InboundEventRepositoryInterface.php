<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\InboundEvent;

/**
 * @class InboundEventRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
interface InboundEventRepositoryInterface
{
    /**
     * @param InboundEvent $event
     * @return int
     */
    public function save(InboundEvent $event): string;
}
