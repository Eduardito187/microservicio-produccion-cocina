<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class DireccionEntregaCambiadaEvent
 * @package App\Application\Integration\Events
 */
class DireccionEntregaCambiadaEvent
{
        /**
     * @var ?string
     */
    public $paqueteId;

    /**
     * @var ?string
     */
    public $itemDespachoId;

    /**
     * @var ?string
     */
    public $direccionId;

    /**
     * Constructor
     *
     * @param ?string $paqueteId
     * @param ?string $itemDespachoId
     * @param ?string $direccionId
     */
    public function __construct(
        ?string $paqueteId,
        ?string $itemDespachoId,
        ?string $direccionId
    ) {
        $this->paqueteId = $paqueteId;
        $this->itemDespachoId = $itemDespachoId;
        $this->direccionId = $direccionId;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['paqueteId', 'paquete_id']),
            $p->getString(['itemDespachoId', 'item_despacho_id']),
            $p->getString(['direccionId', 'direccion_id'], null, true)
        );
    }
}
