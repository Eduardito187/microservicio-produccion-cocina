<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Enum;

/**
 * @class EstadoPlanificado
 */
enum EstadoPlanificado: string
{
    case PROGRAMADO = 'PROGRAMADO';
    case PROCESANDO = 'PROCESANDO';
    case DESPACHADO = 'DESPACHADO';
}
