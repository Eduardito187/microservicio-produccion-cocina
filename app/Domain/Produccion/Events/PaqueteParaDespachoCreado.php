<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class PaqueteParaDespachoCreado
 * @package App\Domain\Produccion\Events
 */
class PaqueteParaDespachoCreado extends BaseDomainEvent
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
     * @var string|int|null
     */
    private $pacienteId;

    /**
     * @var string|int|null
     */
    private $recetaVersionId;

    /**
     * @var string|int|null
     */
    private $suscripcionId;

    /**
     * Constructor
     *
     * @param string|int|null $paqueteId
     * @param string|int|null $etiquetaId
     * @param string|int|null $ventanaId
     * @param string|int|null $direccionId
     * @param string|int|null $pacienteId
     * @param string|int|null $recetaVersionId
     * @param string|int|null $suscripcionId
     */
    public function __construct(
        string|int|null $paqueteId,
        string|int|null $etiquetaId,
        string|int|null $ventanaId,
        string|int|null $direccionId,
        string|int|null $pacienteId,
        string|int|null $recetaVersionId,
        string|int|null $suscripcionId
    ) {
        parent::__construct($paqueteId);
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
        $this->pacienteId = $pacienteId;
        $this->recetaVersionId = $recetaVersionId;
        $this->suscripcionId = $suscripcionId;
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
            'pacienteId' => $this->pacienteId,
            'recetaVersionId' => $this->recetaVersionId,
            'suscripcionId' => $this->suscripcionId,
        ];
    }
}
