<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Enum;

/**
 * @class EstadoPlanificado
 * @package App\Domain\Produccion\Enum
 */
enum EstadoPlanificado: string
{
    case PROGRAMADO = 'PROGRAMADO';
    case PROCESANDO = 'PROCESANDO';
    case DESPACHADO = 'DESPACHADO';
}
