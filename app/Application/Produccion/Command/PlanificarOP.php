<?php

namespace App\Application\Produccion\Command;

class PlanificarOP
{
    /**
     * @var int
     */
    public readonly string $ordenProduccionId;

    /**
     * @var int
     */
    public readonly string $estacionId;

    /**
     * @var int
     */
    public readonly string $recetaVersionId;

    /**
     * @var int
     */
    public readonly string $porcionId;

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



