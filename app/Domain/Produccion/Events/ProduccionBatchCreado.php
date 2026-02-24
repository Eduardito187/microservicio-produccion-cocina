<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class ProduccionBatchCreado
 * @package App\Domain\Produccion\Events
 */
class ProduccionBatchCreado extends BaseDomainEvent
{

    /**
     * @var string|int|null
     */
    private $ordenProduccionId;

    /**
     * @var string|int|null
     */
    public $productoId;

    /**
     * @var string|int|null
     */
    public $porcionId;

    /**
     * @var Qty
     */
    private $qty;

    /**
     * @var int
     */
    public $posicion;

    /**
     * Constructor
     *
     * @param string|int|null $id
     * @param string|int|null $ordenProduccionId
     * @param string|int|null $productoId
     * @param string|int|null $porcionId
     * @param Qty $qty
     * @param int $posicion
     */
    public function __construct(
        string|int|null $id,
        string|int|null $ordenProduccionId,
        string|int|null $productoId,
        string|int|null $porcionId,
        Qty $qty,
        int $posicion
    ) {
        parent::__construct($id);
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productoId = $productoId;
        $this->porcionId = $porcionId;
        $this->qty = $qty;
        $this->posicion = $posicion;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ordenProduccionId' => $this->ordenProduccionId !== null ? (string) $this->ordenProduccionId : null,
            'productoId' => $this->productoId !== null ? (string) $this->productoId : null,
            'porcionId' => $this->porcionId !== null ? (string) $this->porcionId : null,
            'qty' => $this->qty->value(),
            'posicion' => $this->posicion
        ];
    }
}
