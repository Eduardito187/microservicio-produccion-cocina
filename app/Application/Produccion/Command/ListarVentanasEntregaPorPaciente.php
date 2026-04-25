<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarVentanasEntregaPorPaciente
 */
class ListarVentanasEntregaPorPaciente
{
    /**
     * @var string
     */
    public $pacienteId;

    public function __construct(string $pacienteId)
    {
        $this->pacienteId = $pacienteId;
    }
}
