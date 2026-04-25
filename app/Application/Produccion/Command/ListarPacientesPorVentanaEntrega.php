<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarPacientesPorVentanaEntrega
 */
class ListarPacientesPorVentanaEntrega
{
    /**
     * @var string
     */
    public $ventanaEntregaId;

    public function __construct(string $ventanaEntregaId)
    {
        $this->ventanaEntregaId = $ventanaEntregaId;
    }
}
