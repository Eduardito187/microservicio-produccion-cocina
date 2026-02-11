<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class PlanificarOP
 * @package App\Application\Produccion\Command
 */
class PlanificarOP
{
    /**
     * @var int
     */
    public string $ordenProduccionId;

    /**
     * @var int
     */
    public string $estacionId;

    /**
     * @var int
     */
    public string $recetaVersionId;

    /**
     * @var int
     */
    public string $porcionId;

    /**
     * Constructor
     *
     * @param array $dataApi
     */
    public function __construct(
        array $dataApi
    ) {
        $this->ordenProduccionId = $dataApi["ordenProduccionId"];
        $this->estacionId = $dataApi["estacionId"];
        $this->recetaVersionId = $dataApi["recetaVersionId"];
        $this->porcionId = $dataApi["porcionId"];
    }
}
