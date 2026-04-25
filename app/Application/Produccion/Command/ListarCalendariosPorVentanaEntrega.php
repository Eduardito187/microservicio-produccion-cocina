<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ListarCalendariosPorVentanaEntrega
 */
class ListarCalendariosPorVentanaEntrega
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
