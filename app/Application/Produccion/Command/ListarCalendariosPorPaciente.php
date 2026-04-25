<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarCalendariosPorPaciente
 */
class ListarCalendariosPorPaciente
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
