<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class PaqueteActualizado
 * @package App\Domain\Produccion\Events
 */
class PaqueteActualizado extends BaseDomainEvent
{
    /**
     * @var string|int|null
     */
    private $etiquetaId;

    /**
     * @var string|int|null
     */
    private $ventanaId;

    /**
     * @var string|int|null
     */
    private $direccionId;

    /**
     * Constructor
     *
     * @param string|int|null $paqueteId
     * @param string|int|null $etiquetaId
     * @param string|int|null $ventanaId
     * @param string|int|null $direccionId
     */
    public function __construct(
        string|int|null $paqueteId,
        string|int|null $etiquetaId,
        string|int|null $ventanaId,
        string|int|null $direccionId
    ) {
        parent::__construct($paqueteId);
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'etiquetaId' => $this->etiquetaId,
            'ventanaId' => $this->ventanaId,
            'direccionId' => $this->direccionId,
        ];
    }
}
