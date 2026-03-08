<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class PlanificarOP
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
    public $porcionId;

    /**
     * Constructor
     */
    public function __construct(
        array $dataApi
    ) {
        $this->ordenProduccionId = $dataApi['ordenProduccionId'];
        $this->porcionId = $dataApi['porcionId'];
    }
}
