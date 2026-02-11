<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ProcesadorOP
 * @package App\Application\Produccion\Command
 */
class ProcesadorOP
{
    /**
     * @var string
     */
    public $opId;

    /**
     * Constructor
     *
     * @param string $opId
     */
    public function __construct(
        string $opId
    ) {
        $this->opId = $opId;
    }
}
