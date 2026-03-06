<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class CalendarioEntregaCreadoEvent
 * @package App\Application\Integration\Events
 */
class CalendarioEntregaCreadoEvent
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $fecha;

    /**
     * @var ?string
     */
    public $hora;

    /**
     * @var ?string
     */
    public $entregaId;

    /**
     * @var ?string
     */
    public $contratoId;

    /**
     * @var ?int
     */
    public $estado;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $fecha
     * @param ?string $hora
     * @param ?string $entregaId
     * @param ?string $contratoId
     * @param ?int $estado
     */
    public function __construct(
        string $id,
        string $fecha,
        ?string $hora = null,
        ?string $entregaId = null,
        ?string $contratoId = null,
        ?int $estado = null
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->entregaId = $entregaId;
        $this->contratoId = $contratoId;
        $this->estado = $estado;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        // 'fecha' puede venir como 'fecha', 'date' o derivarse de 'occurredOn'
        $fecha = $p->getString(['fecha', 'date']);
        if ($fecha === null) {
            $occurredOn = $p->getString(['occurredOn', 'occurred_on']);
            if ($occurredOn !== null) {
                // Extrae la parte de fecha del timestamp ISO (YYYY-MM-DD)
                $fecha = substr($occurredOn, 0, 10);
            }
        }
        if ($fecha === null) {
            throw new \InvalidArgumentException('Missing required field: fecha|date|occurredOn');
        }

        // 'entregaId' puede venir como entregaId, entrega_id o suscripcionId
        $entregaId = $p->getString(['entregaId', 'entrega_id', 'suscripcionId']);

        // 'id' puede venir como id, calendarioId, calendario_id;
        // si no está, se genera a partir de entregaId + fecha
        $id = $p->getString(['id', 'calendarioId', 'calendario_id']);
        if ($id === null && $entregaId !== null) {
            $id = self::buildCalendarId($entregaId, $fecha);
        }
        if ($id === null) {
            throw new \InvalidArgumentException('Missing required field: id|calendarioId|entregaId|suscripcionId');
        }

        return new self(
            $id,
            $fecha,
            $p->getString(['hora', 'time']),
            $entregaId,
            $p->getString(['contratoId', 'contrato_id']),
            $p->getInt(['estado', 'status'])
        );
    }

    /**
     * @param string $entregaId
     * @param string $fecha
     * @return string
     */
    private static function buildCalendarId(string $entregaId, string $fecha): string
    {
        $hash = md5($entregaId . '|' . $fecha);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }
}
