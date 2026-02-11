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
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $desde;

    /**
     * @var DateTimeImmutable
     */
    public $hasta;

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
