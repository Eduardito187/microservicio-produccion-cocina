<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarPacientesPorCalendario
 */
class ListarPacientesPorCalendario
{
    /**
     * @var string
     */
    public $calendarioId;

    public function __construct(string $calendarioId)
    {
        $this->calendarioId = $calendarioId;
    }
}
