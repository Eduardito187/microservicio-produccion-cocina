<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Entity;

use DateTimeImmutable;

/**
 * @class VentanaEntrega
 * @package App\Domain\Produccion\Entity
 */
class VentanaEntrega
{
    /**
     * @var string|int|null
     */
    public string|int|null $id;

    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $desde;

    /**
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $hasta;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param DateTimeImmutable $desde
     * @param DateTimeImmutable $hasta
     */
    public function __construct(
        string|int|null $id,
        DateTimeImmutable $desde,
        DateTimeImmutable $hasta
    ) {
        $this->id = $id;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }
}
