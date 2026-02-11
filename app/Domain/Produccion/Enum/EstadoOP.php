<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Enum;

/**
 * @class EstadoOP
 * @package App\Domain\Produccion\Enum
 */
enum EstadoOP: string
{
    case CREADA = 'CREADA'; //CREA LA ORDE
    case PLANIFICADA = 'PLANIFICADA'; //PLANIFICA LOS BATCH
    case EN_PROCESO = 'EN_PROCESO'; //CAMBIA ESTADO DE BATCH
    case CERRADA = 'CERRADA';//GENERA DESPACHO
}
