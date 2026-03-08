<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ProcesadorOP
 */
class ProcesadorOP
{
    /**
     * @var string
     */
    public $opId;

    /**
     * Constructor
     */
    public function __construct(
        string $opId
    ) {
        $this->opId = $opId;
    }
}
