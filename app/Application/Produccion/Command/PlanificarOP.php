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
    public $ordenProduccionId;

    /**
     * @var int
     */
    public $estacionId;

    /**
     * @var int
     */
    public $recetaVersionId;

    /**
     * @var int
     */
    public $porcionId;

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
