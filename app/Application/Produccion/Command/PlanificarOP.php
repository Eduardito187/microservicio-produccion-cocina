<?php

namespace App\Application\Produccion\Command;

class PlanificarOP
{
    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var int
     */
    public readonly int $estacionId;

    /**
     * @var int
     */
    public readonly int $recetaVersionId;

    /**
     * @var int
     */
    public readonly int $porcionId;

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



