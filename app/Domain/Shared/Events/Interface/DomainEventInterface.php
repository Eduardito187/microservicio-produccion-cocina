<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Shared\Events\Interface;

use DateTimeImmutable;

/**
 * @class DomainEventInterface
 * @package App\Domain\Shared\Events\Interface
 */
interface DomainEventInterface
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable;

    /**
     * @return string|int|null
     */
    public function aggregateId(): string|int|null;

    /**
     * @return array
     */
    public function toArray(): array;
}
