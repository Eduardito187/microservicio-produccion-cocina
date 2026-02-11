<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class EntregaProgramadaEvent
 * @package App\Application\Integration\Events
 */
class EntregaProgramadaEvent
{
    /**
     * @var string
     */
    public $calendarioId;

    /**
     * @var string
     */
    public $itemDespachoId;

    /**
     * @var ?string
     */
    public $ordenProduccionId;

    /**
     * @var ?array
     */
    public $items;

    /**
     * @var ?array
     */
    public $itemsDespacho;

    /**
     * @var ?string
     */
    public $pacienteId;

    /**
     * @var ?string
     */
    public $direccionId;

    /**
     * @var ?string
     */
    public $ventanaEntregaId;

    /**
     * Constructor
     *
     * @param string $calendarioId
     * @param string $itemDespachoId
     * @param ?string $ordenProduccionId
     * @param ?array $items
     * @param ?array $itemsDespacho
     * @param ?string $pacienteId
     * @param ?string $direccionId
     * @param ?string $ventanaEntregaId
     */
    public function __construct(
        string $calendarioId,
        string $itemDespachoId,
        ?string $ordenProduccionId,
        ?array $items,
        ?array $itemsDespacho,
        ?string $pacienteId,
        ?string $direccionId,
        ?string $ventanaEntregaId
    ) {
        $this->calendarioId = $calendarioId;
        $this->itemDespachoId = $itemDespachoId;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->items = $items;
        $this->itemsDespacho = $itemsDespacho;
        $this->pacienteId = $pacienteId;
        $this->direccionId = $direccionId;
        $this->ventanaEntregaId = $ventanaEntregaId;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['calendarioId', 'calendario_id'], null, true),
            $p->getString(['itemDespachoId', 'item_despacho_id'], null, true),
            $p->getString(['ordenProduccionId', 'orden_produccion_id', 'op_id']),
            $p->getArray(['items']),
            $p->getArray(['itemsDespacho', 'items_despacho']),
            $p->getString(['pacienteId', 'paciente_id']),
            $p->getString(['direccionId', 'direccion_id']),
            $p->getString(['ventanaEntregaId', 'ventana_entrega_id'])
        );
    }
}
